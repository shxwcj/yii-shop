<?php
namespace app\controllers;
use app\controllers\CommonController;
use app\models\User;
use app\models\Cart;
use app\models\Product;
use Yii;

class CartController extends CommonController
{
    public function actionIndex()
    {
        $this->layout = 'layout1';
        if (Yii::$app->session['isLogin'] != 1) {
            return $this->redirect(['member/auth']);
        }
//        $userid = User::find()->where('username = :name', [':name' => Yii::$app->session['loginname']])->one()->userid;
        $userid = User::find()->where(['username'=> Yii::$app->session['loginname']])->one()->userid;
//        $cart = Cart::find()->where('userid = :uid', [':uid' => $userid])->asArray()->all();
        $cart = Cart::find()->where(['userid'=> $userid])->asArray()->all();
        $data = [];
        foreach ($cart as $k=>$pro) {
//            $product = Product::find()->where('productid = :pid', [':pid' => $pro['productid']])->one();
            $product = Product::findOne(['productid' => $pro['productid']]);
            $data[$k]['cover'] = $product->cover;
            $data[$k]['title'] = $product->title;
            $data[$k]['productnum'] = $pro['productnum'];
            $data[$k]['price'] = $pro['price'];
            $data[$k]['productid'] = $pro['productid'];
            $data[$k]['cartid'] = $pro['cartid'];
        }
        return $this->render("index", ['data' => $data]);
    }

    /**
     * 添加购物车
     * @return \yii\web\Response
     */
    public function actionAdd()
    {
        if (Yii::$app->session['isLogin'] != 1) {
            return $this->redirect(['member/auth']);
        }
//        $userid = User::find()->where('username = :name', [':name' => Yii::$app->session['loginname']])->one()->userid;
        $userid = User::find()->where(['username' => Yii::$app->session['loginname']])->one()->userid;
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $num = Yii::$app->request->post()['productnum'];
            $data['Cart'] = $post;
            $data['Cart']['userid'] = $userid;
        }
        if (Yii::$app->request->isGet) {
            $productid = Yii::$app->request->get("productid");
//            $model = Product::find()->where('productid = :pid', [':pid' => $productid])->one();
            $model = Product::find()->where(['productid' => $productid])->one();
            $price = $model->issale ? $model->saleprice : $model->price;
            $num = 1;
            $data['Cart'] = ['productid' => $productid, 'productnum' => $num, 'price' => $price, 'userid' => $userid];
        }
//        if (!$model = Cart::find()->where('productid = :pid and userid = :uid', [':pid' => $data['Cart']['productid'], ':uid' => $data['Cart']['userid']])->one()) {
        if (!$model = Cart::find()->where(['productid'=>$data['Cart']['productid']])->andWhere(['userid' => $data['Cart']['userid']])->one()) {
            $model = new Cart;
        } else {
            $data['Cart']['productnum'] = $model->productnum + $num;
        }
        $data['Cart']['createtime'] = time();
        $model->load($data);
        $model->save();
        return $this->redirect(['cart/index']);
    }

    /**
     * 购物车数量编辑
     */
    public function actionMod()
    {
        $cartid = Yii::$app->request->get("cartid");
        $productnum = Yii::$app->request->get("productnum");
//        Cart::updateAll(['productnum' => $productnum], 'cartid = :cid', [':cid' => $cartid]);
        Cart::updateAll(['productnum' => $productnum], ['cartid' => $cartid]);
    }

    /**
     * 删除购物车
     * @return \yii\web\Response
     */
    public function actionDel()
    {
        $cartid = Yii::$app->request->get("cartid");
//        Cart::deleteAll('cartid = :cid', [':cid' => $cartid]);
        Cart::deleteAll(['cartid' => $cartid]);
        return $this->redirect(['cart/index']);
    }

}





