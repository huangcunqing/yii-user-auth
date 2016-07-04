<?php
require 'IDNA.php';
/**
 * 纳网接口封装
 *
 * @update Davin <wenjiebao@xmisp.com> 2016.06.29 新对接
 */
class Domain_Nawang {
	private $config;
	private $url;
	private $client;
	private $passw;

	public function __construct() {
		$this->url = 'http://api.nabla.local';
		$this->client = '356878';
		$this->passw = '123456';

		$this->debug = false;

//		$this->registerMapper  = new Application_Model_DomainRegisterMapper();	//域名api日志表
	}

	/**
	 * 生成签名
	 * @param $memberId 注册的会员ID
	 * @param $password 申请的API密码
	 * @param $timestamp 本地需上传的时间戳
	 * @return string 本地需上传的签名
	 */
	private function generateSign($memberId, $password, $timestamp){
		$tmpStr = $memberId.md5($password).$timestamp.'NAWANG';
		$tmpStr = md5($tmpStr);

		return $tmpStr;
	}
	/**
	 * 查询
	 *
	 * @param string $domain 域名名称
	 * @param string $suffix 域名后缀
	 * @return int 1, 未注册, 2, 已注册, 3, 查询失败
	 */
	public function check($domain, $suffix) {
		$url = $this->url . '/domain/check';
		$timestamp = time();
		$domain = strtolower(trim($domain));
		$suffix = strtolower(trim($suffix));
		$domain = Domain_IDNA::encode($domain);

		if (!$domain || !$suffix) return $this->result = 'Invalid Params! Domain or domian suffix done not found!';

		$signature = $this->generateSign($this->client, $this->passw, $timestamp);

		$postData = array(
			'keyword' => $domain.'.'.$suffix,
			'timestamp'   => $timestamp,
			'signature'    => $signature,
			'member_id' => $this->client
		);
		if ($this->debug){
			$result = '{"version":"1.0","code":"0000000","message":"check成功","data":{"stateCode":1}}';
		}else{
			$result = Tools_String::request_curl($url, $postData);
		}
		if (!$result) return $this->result = 'REMOTE::ERROR::NORETURN_API_NAWANG'; //远程调用失败， 请求无返回
		$this->result = json_decode($result);

		if(isset($this->result->code) && $this->result->code === '0000000' && isset($this->result->data) && isset($this->result->data->stateCode)){
			return $this->result->data->stateCode;  // 1 为未注册， 2 为已注册
		}
		//查询失败
		return 3;
	}

	/**
	 * 域名注册逻辑
	 * @param array $data
	 * @return int | string 1: 注册成功 2: 注册失败返回 3: 非实时开通返回, 等待成功 4: 未知错误
	 * */
	public function register (array $data) {

		$url = $this->url . '/domain/register';

		$postData = array();
		$timestamp = time();
		$signature = $this->generateSign($this->client, $this->passw, $timestamp);
		$cityCode = $this->getCityCode($data['CityCn']);
		if(!$cityCode){   //获取城市信息失败
			return $this->result = 'REMOTE::ERROR::NORETURN_API_NAWANG'; //远程调用失败， 请求无返回
		}

		//注册表单
		$postData['member_id'] = $this->client;   						//会员ID
		$postData['timestamp'] = $timestamp;
		$postData['signature'] = $signature;
		$postData['keyword'] = Domain_IDNA::encode($data['domain']);		//产品关键词
		$postData['regYear'] = $data['year'];								//注册年限
		$postData['man_type'] = empty($data['Reg_Org_Name']) ? 2 : 1;	//1表示企业/单位,2表示个人 如果注册单位名称（拼音）为NULL，则为个人
		$postData['register_cn'] = $data['Reg_Name'];					//注册者(必须包含汉字2-60字)
		$postData['register_en'] = $data['Reg_EN_Name'];				//注册者[英文] 英文字母（a-z，不区分大小写）空格组成,须包括空格。5-255字
		$postData['link_man_cn'] = $data['Mag_Ch_Name'];				//联系人[中文] 必须包含汉字；【当所有者性质为个人时，可不传】(2到15字)
		$postData['link_man_en'] = $data['Mag_En_Name'];				//联系人[英文] 英文字母（a-z，不区分大小写）与空格组成,须包括空格。；【当所有者性质为个人时，可不传，统一取注册者信息】
		$postData['card_id'] = $data['card_id'];							//身份证号  注册国内域名为必填项 TODO 没有设置该字段，需要设置
		$postData['tel'] = $data['TelArea'].'-'.$data['TelNumber'];		//电话 首位为0,包含一个"-"，其它为数字, 如021-32382324
		$postData['phone'] = $data['phone'];								//手机 数字组成，首字母为1 TODO 没有单独设置该字段，需要设置
		$postData['fax'] = $data['FaxArea'].'-'.$data['FaxNumber'];		//传真 首位为0,包含一个"-"，其它为数字
		$postData['email'] = $data['MagEmail'];							//电子邮箱 符合邮件地址标准
		$postData['city'] = $cityCode;										//城市(城市代码，可通过城市API接口获取)
		//$postData['area'] = $data['year'];								//非必填 县区（城市对应的县区，若为空则默认填充）
		$postData['address_cn'] = $data['ChStreet'];						//地址	须包含汉字 6-80字
		$postData['address_en'] = $data['EnStreet'];						//地址[英文] 英文字母（a-z，不区分大小写）、数字（0-9）、以及”-“（英文中的连词号，即中横线），空格组成须包括空格。8-150字
		$postData['code'] = $data['Zipcode'];								//邮编
		$postData['dns1'] = $data['dns1'];								//必须已经注册进注册局的dns；如果使用纳网dns，则填写ns.nagor.cn； 英文字母（a-z，不区分大小写）、数字（0-9）、以及”-““.”（英文中的连词号，即中横线,英文.）,必须至少一个.
		$postData['dns2'] = $data['dns2'];								//必须已经注册进注册局的dns；如果使用纳网dns，则填写ns.nagor.cn； 英文字母（a-z，不区分大小写）、数字（0-9）、以及”-““.”（英文中的连词号，即中横线,英文.）,必须至少一个.

		$postData = array_map('trim', $postData);
		//URL构造
		$toUrl = $url . '?';
		$toUrl .= $uri = http_build_query($postData);

		if (@iconv('gbk', 'utf-8', $data['domain'])) $sDomain = iconv('gbk', 'utf-8', $data['domain']);
		else $sDomain = $data['domain'];
		//日志
		$logs = $this->registerMapper->set($data['order_id'], $sDomain, 1, 1, iconv('gbk', 'utf-8', $toUrl));
		//调试模式

		if ($this->debug){
			$result = '{"version":"1.0","code":"0000000","message":"注册成功","data":{"order_id":"5524","state":"361","msg":"订单已提交到注册局，等待注册局审核结果"}}';
		}else{
			$result = Tools_String::request_curl($url, $postData);
		}
		//日志, 返回结果
		$this->registerMapper->setResult($data['order_id'], $sDomain, $logs, 1, $result);

		if (!$result) return $this->result = 'REMOTE::ERROR::NORETURN_API_NAWANG'; //远程调用失败， 请求无返回
		$this->result = json_decode($result);

		if(isset($this->result->code) && isset($this->result->message)){
			if($this->result->code === '0000000' && isset($this->result->data) && isset($this->result->data->state)){
				$state = $this->result->data->state;
				if($state == "364"){    //注册成功
					return 1;
				}else if(in_array($state, array("359","360","361"))){ //非实时开通返回, 等待成功  359: 内部审核 360: 运营处理 361: 接口审核中
					return 3;
				}
			}else{
				return 2;  //注册失败返回
			}
		}

		//未知错误
		return 4;
	}

	/**
	 * 续费
	 *
	 * @param array $data
	 * @return bool|string  true: 成功， false: 失败
	 */
	public function renew($data) {
		$url = $this->url . '/domain/ProductRenew';
		$postData = array();
		$timestamp = time();
		$signature = $this->generateSign($this->client, $this->passw, $timestamp);

		$postData['member_id'] = $this->client;   						//会员ID
		$postData['timestamp'] = $timestamp;
		$postData['signature'] = $signature;
		$postData['keyword'] = Domain_IDNA::encode($data['domain']);		//产品关键词
		$postData['renewals_count'] = $data['year'];								//注册年限

		$toUrl = $url . '?';
		$toUrl .= $uri = http_build_query($postData);
		$toUrl = iconv('gbk', 'utf-8', $toUrl);

		if (@iconv('gbk', 'utf-8', $data['domain'])) $sDomain = iconv('gbk', 'utf-8', $data['domain']);
		else $sDomain = $data['domain'];

		$logs = $this->registerMapper->set($data['order_id'], $sDomain, 1, 2, iconv('gbk', 'utf-8', $toUrl));

		if ($this->debug){
			$result = '{"version":"1.0","code":"0000000","message":"产品续费订单提交成功","data":{"order_id":"5524","state":"361","msg":"订单提交成功，等待客服人员处理订单"}}';
		}else{
			$result = Tools_String::request_curl($url, $postData);
		}
		//日志, 返回结果
		$this->registerMapper->setResult($data['order_id'], $sDomain, $logs, 2, $result);

		if (!$result) return $this->result = 'REMOTE::ERROR::NORETURN_API_NAWANG'; //远程调用失败， 请求无返回
		$this->result = json_decode($result);

		if(isset($this->result->code) && isset($this->result->message) && $this->result->code === '0000000'){	//注册成功
			return true;
		}
		//注册失败
		return false;
	}

	/**
	 * 修改DNS
	 * @param array $dns dns列表， 最多6个
	 * @param string $domain 域名
	 * @return bool|string  true: 成功， false: 失败
	 */
	public function changeDNS (array $dns, $domain) {

		if(count($dns)< 2) {  //给定的DNS参数太少
			return $this->result = 'REMOTE::ERROR::DNS_ARRAY_LENGTH_TOO_LITTLE';
		}

		$url = $this->url . '/domain/updatedns';
		$postData = array();
		$timestamp = time();
		$signature = $this->generateSign($this->client, $this->passw, $timestamp);

		$postData['member_id'] = $this->client;   						//会员ID
		$postData['timestamp'] = $timestamp;
		$postData['signature'] = $signature;
		$postData['keyword'] = Domain_IDNA::encode($domain);		//产品关键词
		$postData['dns1'] = $dns[0];
		$postData['dns2'] = $dns[1];
		isset($dns[2]) && $postData['dns3'] = $dns[2];
		isset($dns[3]) && $postData['dns4'] = $dns[3];
		isset($dns[4]) && $postData['dns5'] = $dns[4];
		isset($dns[5]) && $postData['dns6'] = $dns[5];

		$toUrl = $url . '?';
		$toUrl .= $uri = http_build_query($postData);
		$toUrl = iconv('gbk', 'utf-8', $toUrl);
		
		if (@iconv('gbk', 'utf-8', $domain)) $sDomain = iconv('gbk', 'utf-8', $domain);
		else $sDomain = $domain;

		$logs = $this->registerMapper->set(0, $sDomain, 1, 3, iconv('gbk', 'utf-8', $toUrl));

		if ($this->debug){
			$result = '{"version":"1.0","code":"0000000","message":"修改dns成功"}';
		}else{
			$result = Tools_String::request_curl($url, $postData);
		}
		//日志, 返回结果
		$this->registerMapper->setResult(0, $sDomain, $logs, 3, $result);

		if (!$result) return $this->result = 'REMOTE::ERROR::NORETURN_API_NAWANG'; //远程调用失败， 请求无返回
		$this->result = json_decode($result);

		if(isset($this->result->code) && isset($this->result->message) && $this->result->code === '0000000'){	//注册成功
			return true;
		}
		//注册失败
		return false;
	}

	/**
	 * 获取城市Code
	 * @param $cityCnName 中文城市名称
	 * @return bool | string false: 获取失败 string: 城市代码
	 */
	private function getCityCode($cityCnName){

		$url = $this->url . '/site/cityinfocode';
		$postData = array();
		$timestamp = time();
		$signature = $this->generateSign($this->client, $this->passw, $timestamp);

		$postData['member_id'] = $this->client;  //会员ID
		$postData['timestamp'] = $timestamp;
		$postData['signature'] = $signature;
		$postData['city_cn'] = $cityCnName;		//中文城市名称

		if ($this->debug){
			$result = '{"version":"1.0","code":"0000000","message":"获取数据成功","data":{"CityCode":"813000"}}';
		}else{
			$result = Tools_String::request_curl($url, $postData);
		}

		if (!$result) return false; //远程调用失败， 请求无返回
		$this->result = json_decode($result);

		if(isset($this->result->code) && $this->result->code === '0000000' && isset($this->result->data) && isset($this->result->data->CityCode)){	//注册成功
			return $this->result->data->CityCode;
		}
		return false;
	}
}


class Tools_String {
	public static function request_curl($url, $postData){
//		echo '<br/>'. $url.'?'.http_build_query($postData).'<br/>';

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

		$output = curl_exec($ch);
		curl_close($ch);

		return $output;
	}
}

$client = new Domain_Nawang();
echo '<br/>-check-----------------------------------------------<br/>';
var_dump($client->check('safds','cn'));
echo '<br/>-regis-----------------------------------------------<br/>';
var_dump($client->register(array(
	'CityCn'=>'00582',
	'domain'=>'safsa.cn',
	'year'=>'1',
	'Reg_Org_Name'=>'pinyin',
	'Reg_Name'=>'中文',
	'Reg_EN_Name'=>'engl ish',
	'Mag_Ch_Name'=>'中文',
	'Mag_En_Name'=>'engl ish',
	'card_id'=>'150423198204094412',
	'TelArea'=>'0592',
	'TelNumber'=>'5993322',
	'phone'=>'13599401234',
	'FaxArea'=>'0592',
	'FaxNumber'=>'5993322',
	'MagEmail'=>'s011821@163.com',
	'ChStreet'=>'中文六个字符',
	'EnStreet'=>'english with space',
	'Zipcode'=>'610000',
	'dns1'=>'safds1.cn',
	'dns2'=>'safds2.cn',

)));
echo '<br/>-renew-----------------------------------------------<br/>';
var_dump($client->renew(array(
	'domain'=>'safds.cn',
	'year'=>10
	)));
echo '<br/>-chadns-----------------------------------------------<br/>';
var_dump($client->changeDNS(array(
	'safds1.cn',
	'safds2.cn'
), 'safds.cn'));
?>
