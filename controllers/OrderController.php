<?php
namespace app\controllers;
use app\controllers\CommonController;
use Yii;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Cart;
use app\models\Product;
use app\models\User;
use app\models\Address;
use app\models\Pay;
use dzer\express\Express;
use yii\db\Exception;

class OrderController extends CommonController
{
    /**
     * 订单列表
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        $this->layout = "layout2";
        if (Yii::$app->session['isLogin'] != 1) {
            return $this->redirect(['member/auth']);
        }
        $loginname = Yii::$app->session['loginname'];
        $userid = User::find()->where('username = :name or useremail = :email', [':name' => $loginname, ':email' => $loginname])->one()->userid;
        $orders = Order::getProducts($userid);
        return $this->render("index", ['orders' => $orders]);
    }

    public function actionCheck()
    {
        if (Yii::$app->session['isLogin'] != 1) {
            return $this->redirect(['member/auth']);
        }
        $orderid = Yii::$app->request->get('orderid');
        $status = Order::find()->where('orderid = :oid', [':oid' => $orderid])->one()->status;
        if ($status != Order::CREATEORDER && $status != Order::CHECKORDER) {
            return $this->redirect(['order/index']);
        }
        $loginname = Yii::$app->session['loginname'];
        $userid = User::find()->where('username = :name or useremail = :email', [':name' => $loginname, ':email' => $loginname])->one()->userid;
        $addresses = Address::find()->where('userid = :uid', [':uid' => $userid])->asArray()->all();
        $details = OrderDetail::find()->where('orderid = :oid', [':oid' => $orderid])->asArray()->all();
        $data = [];
        foreach($details as $detail) {
            $model = Product::find()->where('productid = :pid' , [':pid' => $detail['productid']])->one();
            $detail['title'] = $model->title;
            $detail['cover'] = $model->cover;
            $data[] = $detail;
        }
        $express = Yii::$app->params['express'];
        $expressPrice = Yii::$app->params['expressPrice'];
        $this->layout = "layout1";
        return $this->render("check", ['express' => $express, 'expressPrice' => $expressPrice, 'addresses' => $addresses, 'products' => $data]);
    }

    /**
     * 订单添加
     * @return \yii\web\Response
     */
    public function actionAdd()
    {
        if (Yii::$app->session['isLogin'] != 1) {
            return $this->redirect(['member/auth']);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (Yii::$app->request->isPost) {
                $post = Yii::$app->request->post();
                $ordermodel = new Order;
                $ordermodel->scenario = 'add';
                $usermodel = User::find()->where('username = :name or useremail = :email', [':name' => Yii::$app->session['loginname'], ':email' => Yii::$app->session['loginname']])->one();
                if (!$usermodel) {
                    throw new \Exception();
                }
                $userid = $usermodel->userid;
                $ordermodel->userid = $userid;
                $ordermodel->status = Order::CREATEORDER;
                $ordermodel->createtime = time();
                if (!$ordermodel->save()) {
                    throw new \Exception();
                }
                $orderid = $ordermodel->getPrimaryKey();
                foreach ($post['OrderDetail'] as $product) {
                    $model = new OrderDetail;
                    $product['orderid'] = $orderid;
                    $product['createtime'] = time();
                    $data['OrderDetail'] = $product;
                    if (!$model->add($data)) {
                        throw new \Exception();
                    }
                    Cart::deleteAll('productid = :pid' , [':pid' => $product['productid']]);
                    Product::updateAllCounters(['num' => -$product['productnum']], 'productid = :pid', [':pid' => $product['productid']]);
                }
            }
            $transaction->commit();
        }catch(\Exception $e) {
            $transaction->rollback();
            return $this->redirect(['cart/index']);
        }
        return $this->redirect(['order/check', 'orderid' => $orderid]);
    }

    /**
     * 确认订单
     * @return \yii\web\Response
     */
    public function actionConfirm()
    {
        //addressid, expressid, status, amount(orderid,userid)
        try {
            if (Yii::$app->session['isLogin'] != 1) {
                return $this->redirect(['member/auth']);
            }
            if (!Yii::$app->request->isPost) {
                throw new \Exception('提交方式错误');
            }
            $post = Yii::$app->request->post();
            $loginname = Yii::$app->session['loginname'];
            $usermodel = User::find()->where('username = :name or useremail = :email', [':name' => $loginname, ':email' => $loginname])->one();
            if (empty($usermodel)) {
                throw new \Exception('该用户不存在');
            }
            $userid = $usermodel->userid;
            $model = Order::find()->where('orderid = :oid and userid = :uid', [':oid' => $post['orderid'], ':uid' => $userid])->one();
            if (empty($model)) {
                throw new \Exception('不存在此订单');
            }
            $model->scenario = "update";
            $post['status'] = Order::CHECKORDER;
            $details = OrderDetail::find()->where('orderid = :oid', [':oid' => $post['orderid']])->all();
            $amount = 0;
            foreach($details as $detail) {
                $amount += $detail->productnum*$detail->price;
            }
            if ($amount <= 0) {
                throw new \Exception('订单数量不能小于0');
            }
            $express = Yii::$app->params['expressPrice'][$post['expressid']];
            if ($express < 0) {
                throw new \Exception('运费不能小于0');
            }
            $amount += $express;
            $post['amount'] = $amount;
            $data=[];
            $data['Order'] = $post;
            $data['Order']['addressid'] = $post['addressid']?:0;
//            var_dump($data['Order']);
//            var_dump($model->load($data));
//            exit();
            $model->save();
            return $this->redirect(['order/pay', 'orderid' => $post['orderid'], 'paymethod' => $post['paymethod']]);
//
//            if ($model->load($data['Order'])&& $model->save()) {
//                return $this->redirect(['order/pay', 'orderid' => $post['orderid'], 'paymethod' => $post['paymethod']]);
//            }
        }catch(\Exception $e) {
            return $this->redirect(['index/index']);
        }
    }

    /**
     * 订单支付
     * @return void|\yii\web\Response
     */
    public function actionPay()
    {
        try{
            if (Yii::$app->session['isLogin'] != 1) {
                throw new \Exception('未登录，请先登录');
            }
//            var_dump(Yii::$app->request->get());exit();
            $orderid = Yii::$app->request->get('orderid');
            $paymethod = Yii::$app->request->get('paymethod');
            if (empty($orderid) || empty($paymethod)) {
                throw new \Exception('订单号以及支付方式不能为空');
            }
            if ($paymethod == 'alipay') {
                return Pay::alipay($orderid);
            }
        }catch(\Exception $e) {}
        return $this->redirect(['order/index']);
    }

    /**
     * 物流信息
     * @throws \Exception
     */
    public function actionGetexpress()
    {
        try{
            $expressno = Yii::$app->request->get('expressno');
            $res = Express::search($expressno);
            echo $res;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 确认收货
     * @return \yii\web\Response
     */
    public function actionReceived()
    {
        $orderid = Yii::$app->request->get('orderid');
        $order = Order::find()->where('orderid = :oid', [':oid' => $orderid])->one();
        if (!empty($order) && $order->status == Order::SENDED) {
            $order->status = Order::RECEIVED;
            $order->save();
        }
        return $this->redirect(['order/index']);
    }

}








