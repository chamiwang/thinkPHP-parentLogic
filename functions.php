/**
 * 实例化模型类 格式 [资源://][模块/]模型
 * @param string $name 资源地址
 * @param string $layer 模型层名称
 * @return Model
 */
function D($name='',$layer='') {

    if(empty($name)) return new Think\Model;

    if(!$layer) {
        static $_model  =   array();
    }
/*    static $_model  =   array();*/
    $layer          =   $layer? : C('DEFAULT_M_LAYER');

    if(!$layer) {
        if(isset($_model[$name.$layer]))
                return $_model[$name.$layer];
    }
/*    if(isset($_model[$name.$layer]))
        return $_model[$name.$layer];*/
    $class          =   parse_res_name($name,$layer);
    if(class_exists($class)) {
        $model      =   new $class(basename($name));
    }elseif(false === strpos($name,'/')){
        // 自动加载公共模块下面的模型
        if(!C('APP_USE_NAMESPACE')){
            import('Common/'.$layer.'/'.$class);
        }else{
            $class      =   '\\Common\\'.$layer.'\\'.$name.$layer;
        }
        $model      =   class_exists($class)? new $class($name) : new Think\Model($name);
    }else {
        Think\Log::record('D方法实例化没找到模型类'.$class,Think\Log::NOTICE);
        $model      =   new Think\Model(basename($name));
    }
    if(!$layer) {
        $_model[$name.$layer]  =  $model;
    }
/*    $_model[$name.$layer]  =  $model;*/
    return $model;
}
