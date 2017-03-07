# thinkPHP-parentLogic
TP3xx的共同逻辑类
# 共通逻辑SampleLogic使用说明
SampleLogic类是将逻辑层进行对象化封装，并提出增删改查方法的基本共通方法的类。

## 导入
把SampleLogic类下载到App/Common/Logic下面
## 用法

1、新建任意Logic并继承SampleLogic

``` 
class OrderRefundLogic extends SampleLogic
{
}
``` 

2、声明该逻辑所操作的模型对象
``` 
class OrderRefundLogic extends SampleLogic
{
    /**
     * @return string
     * 逻辑调用的模型名称
     */
    protected function getLogicName()
    {
        return 'Order/OrderRefund';
    }
}
``` 

## 函数调用案例

### 创建记录（增）

``` 
        // 声明操作的对象逻辑Pgs/PgsAnswer
        $model = D("Pgs/PgsAnswer", "Logic");
        // 开启事务，使数据模型操作失败时自动回滚
        $model->startTrans();
        // 创建多条记录。 如果创建单条记录时create的第二个参数不填，默认为创建单条
        $res = $model->create($data['answer'], false);
        if($res['status'] == RETURN_SUCC) {
            // 创建成功时，Pgs/PgsExamine模型生成一条新的记录
            $res = D("Pgs/PgsExamine", "Logic")->create($data['examine']);
            if($res['status'] == RETURN_SUCC) {
                // 两个模型都创建成功时，结束事务
                $model->commit();
            }
        }
        $this->ajaxReturn($res);
``` 
### 变更记录（删，改）

``` 
       // SampleLogic, 三个参数，第一个是需要变更记录的id， 第二个是变更内容， 第三个是补充的sql 需要以 “ 条件补充”如：“ and user_name='pangmingjun'”
        $this->ajaxReturn(D("Order/OrderRefund", "Logic")->update($id, array('refund_status'=> $type, 'status'=>STATUS_DEL)));
      // 变更多条记录 第一个参数是搜索条件，第二个参数是需要变更的内容
      $where = "user_id = 1";
      $this->ajaxReturn(D("Order/OrderRefund", "Logic")->update($where, array('refund_status'=> $type, 'status'=>STATUS_DEL)));
``` 

### 查询记录（查）
``` 
        $model = D("Order/OrderRefund", "Logic");
        $res = $model->readWhere("seller_id = {$this->getCurrentUserId()}") //查询条件
                                ->readOrder('id desc') // 查询序
                                ->read(1, 10); // 查询从第几页开始，查多少条记录
        
``` 

4， 建议将Common/functions.php文件中的D方法，更新原有D方法(原有D方法可能会导致逻辑层封装后出现bug)
