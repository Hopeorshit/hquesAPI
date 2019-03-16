<?php
/**
 * Created by Byron.
 * User: Administrator
 * Date: 2017/9/26
 * Time:
 建立完整的参数验证层,作为基类使用
 */

namespace api\validate;
use yii\base\InvalidConfigException;
use yii\validators\Validator;

class BaseValidate extends  Validator
{
    public $method=null;
    private $_methodArray=[];

    /**
     * @inheritdoc 继承自父类
     */
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->_methodArray=(array)$this->method;
        if(empty($this->_methodArray)){//判断数组是否为空
            throw new InvalidConfigException('Configuration error :no validating method are found');
        }
        foreach($this->_methodArray as $method){//判断验证规则是否为空
            if(!$this->hasMethod($method)){
                throw new InvalidConfigException('Validating method :\"{$method}\"does not exits!');
            }
        }
    }
    /**
     *@inheritdoc 继承来自父类
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->attributes;//获取到模型的属性
        $this->message = (array)$this->message;//将错误信息转化为数组,数组元素对应[_methodArray]中的错误信息
        foreach ($this->_methodArray as $k => $method) {
            $ret = call_user_func([$this, $method], $value);//把第一个参数作为回调函数使用
            if ($ret == false) {
                $error = isset($this->message[$k]) ? $this->message[$k] : Yii::t('yii', "\"{$value}\" is invalid specified by the validator:" . static::className() . "::$method");
                return $error;
            }
            return null;
        }
    }

        /**
         * @inheritdoc
         */
        protected function validateValue($value)
    {
        $this->message = (array)$this->message;
        foreach($this->_methodArray as $k => $method){
            $ret = call_user_func([$this, $method], $value);
            if($ret === false){
                $error = isset($this->message[$k]) ? $this->message[$k] : Yii::t('yii', "\"{$value}\" is invalid specified by the validator:". static::className() ."::$method");
                return [$error, []];
            }
        }
        return null;
    }

    //一下自定的验证规则
    private static function isNotEmpty($data=null){
        if (empty($data)){
            return false;
        }
        else{
            return true;
        }
    }

    /**
     * 由26个大写英文字母组成的字符串
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function uperchars($data = null)
    {
        $_pattern = "/^[A-Z]+$/";
        return self::_regex($_pattern, $data);
    }

    /**
     * 由26个小写写英文字母组成的字符串
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function lowerchars($data = null)
    {
        $_pattern = "/^[a-z]+$/";
        return self::_regex($_pattern, $data);
    }

    /**
     * 由数字和26个英文字母组成的字符串
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function numschars($data = null)
    {
        $_pattern = "/^[A-Za-z0-9]+$/";
        return self::_regex($_pattern, $data);
    }

    /**
     * 手机号码
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function mobile($data = null)
    {
        $_pattern = "/^(0|86|17951)?(13[0-9]|15[012356789]|1[78][0-9]|14[57])[0-9]{8}$/";
        return self::_regex($_pattern, $data);
    }

    /**
     * Email
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function email($data = null)
    {
        $_res = filter_var($data, FILTER_VALIDATE_EMAIL);
        return empty($_res) ? false : true;
    }

    /**
     * 邮编
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function postcode($data = null)
    {
        $_pattern = "/^[1-9]\d{5}(?!\d)$/";
        return self::_regex($_pattern, $data);
    }

    /**
     * 中文
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function zh($data = null)
    {
        $_pattern = "/^[\x{4e00}-\x{9fa5}]+$/u";
        return self::_regex($_pattern, $data);
    }

    /**
     * URL地址
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function url($data = null)
    {
        $_res = filter_var($data, FILTER_VALIDATE_URL);
        return empty($_res) ? false : true;
    }

    /**
     * 身份证
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function identity($data = null)
    {
        $_pattern = "/^(^\d{15}$)|(^\d{17}([0-9]|X)$)$/";
        return self::_regex($_pattern, $data);
    }

    /**
     * IPv4
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function ip($data = null)
    {
        $_res = filter_var($data, FILTER_VALIDATE_IP);
        return empty($_res) ? false : true;
    }

    /**
     * 匹配正则公共方法
     * @param $pattern string 匹配模式
     * @param $subject string 对象
     * @return bool
     */
    private static function _regex($pattern, $subject = null)
    {
        if ($subject === null)
        {
            return false;
        }
        if (preg_match($pattern, $subject))
        {
            return true;
        }
        return false;
    }


}