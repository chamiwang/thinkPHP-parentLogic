<?php
namespace Common\Logic;
/**
 * Class SampleLogic
 * @package Common\Logic
 * 共通逻辑
 */
class SampleLogic {
    public static $process = '';

    public function __construct()
    {
        $this->model = D($this->getLogicName());
        $this->readWhere = '';
        $this->readFields = '';
        $this->readOrder = '';
        $this->readTable = '';
        $this->id_key = false;
    }

    /**
     * @return string
     * 逻辑调用的模型名称
     */
    protected function getLogicName()
    {
        return '';
    }

    /**
     * 开始事务
     */
    public function startTrans()
    {
        if(!$this::$process) {
            $this::$process = $this->model;
            $this->model->startTrans();
        }
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        $this::$process->commit();
    }

    /**
     * 回滚事务
     */
    public function rollback()
    {
        if($this::$process) {
            $this::$process->rollback();
        }
    }

    /**
     * @param array $data 对象信息
     * @param bool $is_single 是否创建单一对象
     * @return array
     * 创建对象
     */
    public function create($data, $is_single = true)
    {
        if ($is_single) {
            $data = $this->preCreate($data);

            if (false === $this->model->create($data)) {
                $this->rollback();
                return ['status' => RETURN_ERROR, 'value' => "添加失败:".$this->model->getError()];
            }
            $res = $this->model->add();
        } else {
            foreach ($data as &$a_data) {
                $a_data = $this->preCreate($a_data);

                if (false === $this->model->create($a_data)) {
                    $this->rollback();
                    return ['status' => RETURN_ERROR, 'value' => "添加失败:".$this->model->getError()];
                }

            }
            $res = $this->model->addAll($data);
        }

        if ($res) {
            $res = array("status" => RETURN_SUCC, "value" => $res);
            $res = $this->postCreate($res, $data);
            return $res;
        } else {
            if($this::$process) {
                $this::$process->rollback();
            }
            return array("status" => RETURN_ERROR, "value" => "添加失败:".$this->model->getError());
        }
    }

    /**
     * @param array $data 对象信息
     * 删除对象
     */
    public function delete($data)
    {

    }


    /**
     * @param int $id 编号
     * @param array $data 信息
     * @param string $sql 查询
     * @return array
     */
    public function update($id, $data, $sql = '')
    {
        $data = $this->preUpdate($id, $data);

        if (false === $this->model->create($data)) {
            $this->rollback();
            return ['status' => RETURN_ERROR, 'value' => "修改失败:".$this->model->getError()];
        }

        $res = $this->model->where("id = {$id} and status = ".STATUS_OK.$sql)->save();

        if ($res) {
            return array("status" => RETURN_SUCC, "value" => $res);
        } else {
            if($this::$process) {
                $this::$process->rollback();
            }
            return array("status" => RETURN_ERROR, "value" => "修改失败");
        }
    }

    public function s_update($id, $data, $sql = '')
    {
        $data = $this->preUpdate($id, $data);
        $res = $this->model->where("id = {$id}".$sql)->save($data);
        if ($res) {
            return array("status" => RETURN_SUCC, "value" => $res);
        } else {
            if($this::$process) {
                $this::$process->rollback();
            }
            return array("status" => RETURN_ERROR, "value" => "修改失败");
        }
    }

    public function mutiUpdate($where, $data)
    {
        $res = $this->model->where($where." and status = ".STATUS_OK)->save($data);
        if ($res) {
            return array("status" => RETURN_SUCC, "value" => $res);
        } else {
            if($this::$process) {
                $this::$process->rollback();
            }
            return array("status" => RETURN_ERROR, "value" => "修改失败");
        }
    }

    public function multiUpdate($where, $data)
    {
        return $this->mutiUpdate($where, $data);
    }
    /**
     * @param string $sql 查询语句
     * @return $this
     * 查询条件
     */
    public function readWhere($sql)
    {
        $this->readWhere .= $sql;
        return $this;
    }

    public function readTable($sql)
    {
        $this->readTable .= $sql;
        return $this;
    }
    /**
     * @param string $fields 查询字段名
     * @return $this
     * 查询某些字段
     */
    public function readFields($fields, $id_key = false)
    {
        $this->readFields = $fields;
        $this->id_key = $id_key;
        return $this;
    }

    /**
     * @param $fields
     * 查询顺序
     */
    public function readOrder($fields)
    {
        $this->readOrder = $fields;
        return $this;
    }

    /**
     * @param string $field 求和字段名
     * @param string $sql 附加条件
     * @return mixed
     * 查询某字段总和
     */
    public function readSum($field, $sql = "")
    {
        return $this->model->where($this->readWhere.$sql." and status = ".STATUS_OK)->getField("sum({$field}) as sum");
    }

    public function find($id)
    {
        return $this->model->where("status = ".STATUS_OK)->find($id);
    }

    public function findBy($condition,$order = "")
    {
        if($order == ""){
            $order = "id desc";
        }
        return $this->model->where($condition." and status = ".STATUS_OK)->order($order)->find();
    }
    /**
     * @param $limit_start
     * @param $limit_num
     * @return array
     * 查询
     */
    public function read($limit_start, $limit_num,$max_id = "")
    {
        if(!$this->readWhere) {
            $this->readWhere = '1=1';
        }
        if($max_id != ""){
            $this->readWhere .= " and id < {$max_id}";
        }
        $this->readWhere .= " and status = ".STATUS_OK;
        $res = $this->model;


        if ($this->readTable) {
            $res = $res->table($this->readTable);
        }

        if ($this->readWhere) {
            $res = $res->where($this->readWhere);
        }

        if ($this->readFields) {
            $res = $res->field($this->readFields);
        }

        if ($this->readOrder) {
            $res = $res->order($this->readOrder);
        }

        if (($limit_start || $limit_start == 0) && $limit_num) {
            $res = $res->limit($limit_start.",".$limit_num);
        }
        if($this->id_key) {
            $res = $res
                ->getField($this->readFields);
        } else {
            $res = $res
                ->select();
        }

        if ($this->readTable) {
            $count = $this->model->table($this->readTable)->where($this->readWhere)->count();
        } else {
            $count = $this->model->where($this->readWhere)->count();
        }

        if($max_id == ""){
            $max_id = $this->model->where($this->readWhere)->max("id");
        }
        return array("status" => RETURN_SUCC, "value" => array("count" => $count == null ? 0 : $count, "max_id" => $max_id, "list" => $res == null ? array() : $res));
    }

    public function s_read($limit_start, $limit_num)
    {
        if(!$this->readWhere) {
            $this->readWhere = '1=1';
        }
        $res = $this->model;

        if ($this->readTable) {
            $res = $res->table($this->readTable);
        }

        if ($this->readWhere) {
            $res = $res->where($this->readWhere);
        }

        if ($this->readFields) {
            $res = $res->field($this->readFields);

      }

        if ($this->readOrder) {
            $res = $res->order($this->readOrder);
        }

        if (($limit_start || $limit_start == 0) && $limit_num) {
            $res = $res->limit($limit_start.",".$limit_num);
        }

        if($this->id_key) {
            $res = $res
                ->getField($this->readFields);
        } else {
            $res = $res
                ->select();
        }

        if ($this->readTable) {
            $count = $this->model->table($this->readTable)->where($this->readWhere)->count();
        } else {
            $count = $this->model->where($this->readWhere)->count();
        };
        return array("status" => RETURN_SUCC, "value" => array("count" => $count == null ? 0 : $count, "list" => $res == null ? array() : $res));
    }

    /**
     * @param $data
     * @return mixed
     * 创建对象前处理
     */
    public function preCreate($data)
    {
        return $data;
    }

    public function preUpdate($id, $data)
    {
        return $data;
    }

    public function postCreate($res, $data)
    {
        return $res;
    }
}
