<?php

namespace app\modules\controllers;
use app\models\Category;
use app\models\Product;
use yii\web\Controller;
use Yii;
use yii\data\Pagination;
use crazyfd\qiniu\Qiniu;
use app\modules\controllers\CommonController;

class ProductController extends CommonController
{
    /**
     * 商品列表
     * @return string
     */
    public function actionList()
    {
        $this->layout = "layout1";
        $model = Product::find();
        $count = $model->count();
        $pageSize = Yii::$app->params['pageSize']['product'];
        $pager = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);
        $products = $model->offset($pager->offset)->limit($pager->limit)->all();
        return $this->render("products", ['pager' => $pager, 'products' => $products]);
    }

    /**
     * 商品添加
     * @return string
     * @throws \Exception
     */
    public function actionAdd()
    {
        $this->layout = "layout1";
        $model = new Product;
        $cate = new Category;
        $list = $cate->getOptions();
        unset($list[0]);
        
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $pics = $this->upload();
            if (!$pics) {
                $model->addError('cover', '封面不能为空');
            } else {
                $post['Product']['cover'] = $pics['cover'];
                $post['Product']['pics'] = $pics['pics'];
            }
            $post['Product']['saleprice'] = $post['Product']['saleprice']?:0;
            if ($pics && $model->add($post)) {
                Yii::$app->session->setFlash('info', '添加成功');
            } else {
                Yii::$app->session->setFlash('info', '添加失败');
            }
            return $this->redirect(['product/list']);
        }
        return $this->render("add", ['opts' => $list, 'model' => $model]);
    }

    /**
     * 上传文件 七牛
     * @return array|bool
     * @throws \Exception
     */
    private function upload()
    {
        if ($_FILES['Product']['error']['cover'] > 0) {
            return false;
        }
        $qiniu = new Qiniu(Product::AK, Product::SK, Product::DOMAIN, Product::BUCKET);
        $key = uniqid();
        $qiniu->uploadFile($_FILES['Product']['tmp_name']['cover'], $key);
        $cover = $qiniu->getLink($key);
        $pics = [];
        foreach ($_FILES['Product']['tmp_name']['pics'] as $k => $file) {
            if ($_FILES['Product']['error']['pics'][$k] > 0) {
                continue;
            }
            $key = uniqid();
            $qiniu->uploadFile($file, $key);
            $pics[$key] = $qiniu->getLink($key);
        }
        return ['cover' => $cover, 'pics' => json_encode($pics)];
    }

    /**
     * 商品编辑
     * @return string
     * @throws \Exception
     */
    public function actionMod()
    {
        $this->layout = "layout1";
        $cate = new Category;
        $list = $cate->getOptions();
        unset($list[0]);

        $productid = Yii::$app->request->get("productid");
//        $model = Product::find()->where('productid = :id', [':id' => $productid])->one();
        $model = Product::findOne(['productid'=>$productid]);
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $qiniu = new Qiniu(Product::AK, Product::SK, Product::DOMAIN, Product::BUCKET);
            $post['Product']['cover'] = $model->cover;
            if ($_FILES['Product']['error']['cover'] == 0) {
                $key = uniqid();
                $qiniu->uploadFile($_FILES['Product']['tmp_name']['cover'], $key);
                $post['Product']['cover'] = $qiniu->getLink($key);
                $qiniu->delete(basename($model->cover));
            }
            $pics = [];
            foreach($_FILES['Product']['tmp_name']['pics'] as $k => $file) {
                if ($_FILES['Product']['error']['pics'][$k] > 0) {
                    continue;
                }
                $key = uniqid();
                $qiniu->uploadfile($file, $key);
                $pics[$key] = $qiniu->getlink($key);
            }
            $post['Product']['pics'] = json_encode(array_merge((array)json_decode($model->pics, true), $pics));
            if ($model->load($post) && $model->save()) {
                Yii::$app->session->setFlash('info', '修改成功');
            }
            return $this->redirect(['product/list']);
        }
        return $this->render('add', ['model' => $model, 'opts' => $list]);
    }

    /**
     * 图片删除
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionRemovepic()
    {
        $key = Yii::$app->request->get("key");
        $productid = Yii::$app->request->get("productid");
//        $model = Product::find()->where('productid = :pid', [':pid' => $productid])->one();
        $model = Product::findOne(['productid'=>$productid]);
        $qiniu = new Qiniu(Product::AK, Product::SK, Product::DOMAIN, Product::BUCKET);
        $qiniu->delete($key);
        $pics = json_decode($model->pics, true);
        unset($pics[$key]);
//        Product::updateAll(['pics' => json_encode($pics)], 'productid = :pid', [':pid' => $productid]);
        Product::updateAll(['pics' => json_encode($pics)], ['productid'=> $productid]);
        return $this->redirect(['product/mod', 'productid' => $productid]);
    }

    /**
     * 商品删除
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionDel()
    {
        $productid = Yii::$app->request->get("productid");
//        $model = Product::find()->where('productid = :pid', [':pid' => $productid])->one();
        $model = Product::findOne(['productid'=>$productid]);
        $key = basename($model->cover);
        $qiniu = new Qiniu(Product::AK, Product::SK, Product::DOMAIN, Product::BUCKET);
        $qiniu->delete($key);
        $pics = json_decode($model->pics, true);
        foreach($pics as $key=>$file) {
            $qiniu->delete($key);
        }
//        Product::deleteAll('productid = :pid', [':pid' => $productid]);
        Product::deleteAll(['productid'=> $productid]);
        return $this->redirect(['product/list']);
    }

    /**
     * 商品上架
     * @return \yii\web\Response
     */
    public function actionOn()
    {
        $productid = Yii::$app->request->get("productid");
//        Product::updateAll(['ison' => '1'], 'productid = :pid', [':pid' => $productid]);
        Product::updateAll(['ison' => '1'],['productid'=> $productid]);
        return $this->redirect(['product/list']);
    }

    /**
     * 商品下架
     * @return \yii\web\Response
     */
    public function actionOff()
    {
        $productid = Yii::$app->request->get("productid");
//        Product::updateAll(['ison' => '0'], 'productid = :pid', [':pid' => $productid]);
        Product::updateAll(['ison' => '0'],['productid'=> $productid]);
        return $this->redirect(['product/list']);
    }
}
