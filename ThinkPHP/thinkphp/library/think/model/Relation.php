<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\model;

use think\Db;
use think\Exception;
use think\Loader;
use think\model\Pivot;

class Relation
{
    const HAS_ONE         = 1;
    const HAS_MANY        = 2;
    const BELONGS_TO      = 3;
    const BELONGS_TO_MANY = 4;

    // 父模型对象
    protected $parent;
    // 当前关联的模型类
    protected $model;
    // 中间表模型
    protected $middle;
    // 当前关联类型
    protected $type;
    // 关联外键
    protected $foreignKey;
    // 关联键
    protected $localKey;

    /**
     * 架构函数
     * @access public
     * @param \think\Model $model 上级模型对象
     */
    public function __construct($model)
    {
        $this->parent = $model;
    }

    /**
     * 获取当前关联信息
     * @access public
     * @param string $name 关联信息
     * @return array|string|integer
     */
    public function getRelationInfo($name = '')
    {
        $info = [
            'type'       => $this->type,
            'model'      => $this->model,
            'middle'     => $this->middle,
            'foreignKey' => $this->foreignKey,
            'localKey'   => $this->localKey,
        ];
        return $name ? $info[$name] : $info;
    }

    // 获取关联数据
    public function getRelation($name)
    {
        // 执行关联定义方法
        $relation   = $this->parent->$name();
        $foreignKey = $this->foreignKey;
        $localKey   = $this->localKey;
        // 判断关联类型执行查询
        switch ($this->type) {
            case self::HAS_ONE:
                $result = $relation->where($foreignKey, $this->parent->$localKey)->find();
                break;
            case self::BELONGS_TO:
                $result = $relation->where($localKey, $this->parent->$foreignKey)->find();
                break;
            case self::HAS_MANY:
                $result = $relation->where($foreignKey, $this->parent->$localKey)->select();
                break;
            case self::BELONGS_TO_MANY:
                // 关联查询
                $pk                                = $this->parent->getPk();
                $condition['pivot.' . $foreignKey] = $this->parent->$pk;
                $result                            = $this->belongsToManyQuery($relation, $this->middle, $localKey, $foreignKey, $condition)->select();
                foreach ($result as $set) {
                    $pivot = [];
                    foreach ($set->toArray() as $key => $val) {
                        if (strpos($key, '__')) {
                            list($name, $attr) = explode('__', $key, 2);
                            if ('pivot' == $name) {
                                $pivot[$attr] = $val;
                                unset($set->$key);
                            }
                        }
                    }
                    $set->pivot = new Pivot($pivot, $this->middle);
                }
                break;
            default:
                // 直接返回
                $result = $relation;
        }
        return $result;
    }

    /**
     * 预载入关联查询 返回数据集
     * @access public
     * @param array $resultSet 数据集
     * @param string $relation 关联名
     * @return array
     */
    public function eagerlyResultSet($resultSet, $relation)
    {
        $relations = is_string($relation) ? explode(',', $relation) : $relation;

        foreach ($relations as $key => $relation) {
            $subRelation = '';
            $closure     = false;
            if ($relation instanceof \Closure) {
                $closure  = $relation;
                $relation = $key;
            }
            if (strpos($relation, '.')) {
                list($relation, $subRelation) = explode('.', $relation);
            }
            // 执行关联方法
            $model = $this->parent->$relation();
            // 获取关联信息
            $localKey   = $this->localKey;
            $foreignKey = $this->foreignKey;
            switch ($this->type) {
                case self::HAS_ONE:
                case self::BELONGS_TO:
                    foreach ($resultSet as $result) {
                        // 模型关联组装
                        $this->match($this->model, $relation, $result);
                    }
                    break;
                case self::HAS_MANY:
                    $range = [];
                    foreach ($resultSet as $result) {
                        // 获取关联外键列表
                        if (isset($result->$localKey)) {
                            $range[] = $result->$localKey;
                        }
                    }

                    if (!empty($range)) {
                        $data = $this->eagerlyOneToMany($model, [$foreignKey => ['in', $range]], $relation, $subRelation, $closure);

                        // 关联数据封装
                        foreach ($resultSet as $result) {
                            if (isset($data[$result->$localKey])) {
                                $result->__set($relation, $data[$result->$localKey]);
                            } else {
                                $result->__set($relation, []);
                            }
                        }
                    }
                    break;
                case self::BELONGS_TO_MANY:
                    $pk    = $resultSet[0]->getPk();
                    $range = [];
                    foreach ($resultSet as $result) {
                        // 获取关联外键列表
                        if (isset($result->$pk)) {
                            $range[] = $result->$pk;
                        }
                    }

                    if (!empty($range)) {
                        // 查询关联数据
                        $data = $this->eagerlyManyToMany($model, ['pivot.' . $foreignKey => ['in', $range]], $relation, $subRelation);

                        // 关联数据封装
                        foreach ($resultSet as $result) {
                            if (isset($data[$result->$pk])) {
                                $result->__set($relation, $data[$result->$pk]);
                            } else {
                                $result->__set($relation, []);
                            }
                        }
                    }
                    break;
            }
            $this->relation = [];
        }
        return $resultSet;
    }

    /**
     * 预载入关联查询 返回模型对象
     * @access public
     * @param Model $result 数据对象
     * @param string $relation 关联名
     * @return \think\Model
     */
    public function eagerlyResult($result, $relation)
    {
        $relations = is_string($relation) ? explode(',', $relation) : $relation;

        foreach ($relations as $key => $relation) {
            $subRelation = '';
            $closure     = false;
            if ($relation instanceof \Closure) {
                $closure  = $relation;
                $relation = $key;
            }
            if (strpos($relation, '.')) {
                list($relation, $subRelation) = explode('.', $relation);
            }
            // 执行关联方法
            $model      = $this->parent->$relation();
            $localKey   = $this->localKey;
            $foreignKey = $this->foreignKey;
            switch ($this->type) {
                case self::HAS_ONE:
                case self::BELONGS_TO:
                    // 模型关联组装
                    $this->match($this->model, $relation, $result);
                    break;
                case self::HAS_MANY:
                    if (isset($result->$localKey)) {
                        $data = $this->eagerlyOneToMany($model, [$foreignKey => $result->$localKey], $relation, $subRelation, $closure);
                        // 关联数据封装
                        if (!isset($data[$result->$localKey])) {
                            $data[$result->$localKey] = [];
                        }
                        $result->__set($relation, $data[$result->$localKey]);
                    }
                    break;
                case self::BELONGS_TO_MANY:
                    $pk = $result->getPk();
                    if (isset($result->$pk)) {
                        $pk = $result->$pk;
                        // 查询管理数据
                        $data = $this->eagerlyManyToMany($model, ['pivot.' . $foreignKey => $pk], $relation, $subRelation);

                        // 关联数据封装
                        if (!isset($data[$pk])) {
                            $data[$pk] = [];
                        }
                        $result->__set($relation, $data[$pk]);
                    }
                    break;

            }
        }
        return $result;
    }

    /**
     * 一对一 关联模型预查询拼装
     * @access public
     * @param string $model 模型名称
     * @param string $relation 关联名
     * @param Model $result 模型对象实例
     * @return void
     */
    protected function match($model, $relation, &$result)
    {
        $modelName = Loader::parseName(basename(str_replace('\\', '/', $model)));
        // 重新组装模型数据
        foreach ($result->toArray() as $key => $val) {
            if (strpos($key, '__')) {
                list($name, $attr) = explode('__', $key, 2);
                if ($name == $modelName) {
                    $list[$name][$attr] = $val;
                    unset($result->$key);
                }
            }
        }

        if (!isset($list[$modelName])) {
            // 设置关联模型属性
            $list[$modelName] = [];
        }
        $result->__set($relation, new $model($list[$modelName]));
    }

    /**
     * 一对多 关联模型预查询
     * @access public
     * @param object $model 关联模型对象
     * @param array $where 关联预查询条件
     * @param string $relation 关联名
     * @param string $subRelation 子关联
     * @return void
     */
    protected function eagerlyOneToMany($model, $where, $relation, $subRelation = '', $closure = false)
    {
        $foreignKey = $this->foreignKey;
        // 预载入关联查询 支持嵌套预载入
        $list = $model->where($where)->where($closure)->with($subRelation)->select();

        // 组装模型数据
        $data = [];
        foreach ($list as $set) {
            $data[$set->$foreignKey][] = $set;
        }
        return $data;
    }

    /**
     * 多对多 关联模型预查询
     * @access public
     * @param object $model 关联模型对象
     * @param array $where 关联预查询条件
     * @param string $relation 关联名
     * @param string $subRelation 子关联
     * @return void
     */
    protected function eagerlyManyToMany($model, $where, $relation, $subRelation = '')
    {
        $foreignKey = $this->foreignKey;
        $localKey   = $this->localKey;
        // 预载入关联查询 支持嵌套预载入
        $list = $this->belongsToManyQuery($model, $this->middle, $localKey, $foreignKey, $where)->with($subRelation)->select();

        // 组装模型数据
        $data = [];
        foreach ($list as $set) {
            $pivot = [];
            foreach ($set->toArray() as $key => $val) {
                if (strpos($key, '__')) {
                    list($name, $attr) = explode('__', $key, 2);
                    if ('pivot' == $name) {
                        $pivot[$attr] = $val;
                        unset($set->$key);
                    }
                }
            }
            $set->pivot                = new Pivot($pivot, $this->middle);
            $data[$set->$foreignKey][] = $set;
        }
        return $data;
    }

    /**
     * HAS ONE 关联定义
     * @access public
     * @param string $model 模型名
     * @param string $foreignKey 关联外键
     * @param string $localKey 关联主键
     * @return \think\db\Query|string
     */
    public function hasOne($model, $foreignKey, $localKey)
    {
        $this->type       = self::HAS_ONE;
        $this->model      = $model;
        $this->foreignKey = $foreignKey;
        $this->localKey   = $localKey;

        // 返回关联的模型对象
        return $this;
    }

    /**
     * BELONGS TO 关联定义
     * @access public
     * @param string $model 模型名
     * @param string $foreignKey 关联外键
     * @param string $localKey 关联主键
     * @return \think\db\Query|string
     */
    public function belongsTo($model, $foreignKey, $otherKey)
    {
        // 记录当前关联信息
        $this->type       = self::BELONGS_TO;
        $this->model      = $model;
        $this->foreignKey = $foreignKey;
        $this->localKey   = $otherKey;

        // 返回关联的模型对象
        return $this;
    }

    /**
     * HAS MANY 关联定义
     * @access public
     * @param string $model 模型名
     * @param string $foreignKey 关联外键
     * @param string $localKey 关联主键
     * @return \think\db\Query|string
     */
    public function hasMany($model, $foreignKey, $localKey)
    {
        // 记录当前关联信息
        $this->type       = self::HAS_MANY;
        $this->model      = $model;
        $this->foreignKey = $foreignKey;
        $this->localKey   = $localKey;

        // 返回关联的模型对象
        return $this;
    }

    /**
     * BELONGS TO MANY 关联定义
     * @access public
     * @param string $model 模型名
     * @param string $table 中间表名
     * @param string $localKey 当前模型关联键
     * @param string $foreignKey 关联模型关联键
     * @return \think\db\Query|string
     */
    public function belongsToMany($model, $table, $localKey, $foreignKey)
    {
        // 记录当前关联信息
        $this->type       = self::BELONGS_TO_MANY;
        $this->model      = $model;
        $this->foreignKey = $foreignKey;
        $this->localKey   = $localKey;
        $this->middle     = $table;

        // 返回关联的模型对象
        return $this;
    }

    /**
     * BELONGS TO MANY 关联查询
     * @access public
     * @param object $model 关联模型对象
     * @param string $table 中间表名
     * @param string $localKey 当前模型关联键
     * @param string $foreignKey 关联模型关联键
     * @param array $condition 关联查询条件
     * @return \think\db\Query|string
     */
    protected function belongsToManyQuery($model, $table, $localKey, $foreignKey, $condition = [])
    {
        // 关联查询封装
        $tableName  = $model->getTable();
        $relationFk = $model->getPk();
        return $model::field($tableName . '.*')
            ->field(true, false, $table, 'pivot', 'pivot__')
            ->join($table . ' pivot', 'pivot.' . $localKey . '=' . $tableName . '.' . $relationFk)
            ->where($condition);
    }

    /**
     * 保存当前关联数据对象
     * @access public
     * @param mixed $data 数据 可以使用数组 关联模型对象 和 关联对象的主键
     * @param array $pivot 中间表额外数据
     * @return integer
     */
    public function save($data, array $pivot = [])
    {
        // 判断关联类型
        switch ($this->type) {
            case self::HAS_ONE:
            case self::BELONGS_TO:
            case self::HAS_MANY:
                // 保存关联表数据
                $data[$this->foreignKey] = $this->parent->{$this->localKey};
                $model                   = new $this->model;
                return $model->save($data);
            case self::BELONGS_TO_MANY:
                // 保存关联表/中间表数据
                return $this->attach($data, $pivot);
        }
    }

    /**
     * 批量保存当前关联数据对象
     * @access public
     * @param array $dataSet 数据集
     * @param array $pivot 中间表额外数据
     * @return integer
     */
    public function saveAll(array $dataSet, array $pivot = [])
    {
        $result = false;
        foreach ($dataSet as $key => $data) {
            // 判断关联类型
            switch ($this->type) {
                case self::HAS_MANY:
                    $data[$this->foreignKey] = $this->parent->{$this->localKey};
                    $result                  = $this->save($data);
                    break;
                case self::BELONGS_TO_MANY:
                    // TODO
                    $result = $this->attach($data, !empty($pivot) ? $pivot[$key] : []);
                    break;
            }
        }
        return $result;
    }

    /**
     * 附加关联的一个中间表数据
     * @access public
     * @param mixed $data 数据 可以使用数组、关联模型对象 或者 关联对象的主键
     * @param array $pivot 中间表额外数据
     * @return integer
     */
    public function attach($data, $pivot = [])
    {
        if (is_array($data)) {
            // 保存关联表数据
            $model = new $this->model;
            $model->save($data);
            $relationFk = $model->getPk();
            $id         = $model->$relationFk;
        } elseif (is_int($data)) {
            // 根据关联表主键直接写入中间表
            $id = $data;
        } elseif ($data instanceof Model) {
            // 根据关联表主键直接写入中间表
            $relationFk = $data->getPk();
            $id         = $data->$relationFk;
        }

        if ($id) {
            // 保存中间表数据
            $pk                       = $this->parent->getPk();
            $pivot[$this->localKey]   = $this->parent->$pk;
            $pivot[$this->foreignKey] = $id;
            return Db::table($this->middle)->insert($pivot);
        } else {
            throw new Exception(' miss relation data');
        }
    }

    /**
     * 解除关联的一个中间表数据
     * @access public
     * @param integer|array $data 数据 可以使用关联对象的主键
     * @param bool $relationDel 是否同时删除关联表数据
     * @return integer
     */
    public function detach($data, $relationDel = false)
    {
        if (is_array($data)) {
            $id = $data;
        } elseif (is_int($data)) {
            // 根据关联表主键直接写入中间表
            $id = $data;
        } elseif ($data instanceof Model) {
            // 根据关联表主键直接写入中间表
            $relationFk = $data->getPk();
            $id         = $data->$relationFk;
        }
        // 删除中间表数据
        $pk                       = $this->parent->getPk();
        $pivot[$this->localKey]   = $this->parent->$pk;
        $pivot[$this->foreignKey] = is_array($id) ? ['in', $id] : $id;
        Db::table($this->middle)->where($pivot)->delete();

        // 删除关联表数据
        if ($relationDel) {
            $model = $this->model;
            $model::destroy($id);
        }
    }

    public function __call($method, $args)
    {
        if ($this->model) {
            $model = new $this->model;
            $db    = $model->db();
            if (self::HAS_MANY == $this->type && isset($this->parent->{$this->localKey})) {
                // 关联查询带入关联条件
                $db->where($this->foreignKey, $this->parent->{$this->localKey});
            }
            return call_user_func_array([$db, $method], $args);
        } else {
            throw new Exception(__CLASS__ . ':' . $method . ' method not exist');
        }
    }

}
