<?php
/**
 * TOP API: taobao.tbk.dg.vegas.send.status request
 * 
 * @author auto create
 * @since 1.0, 2020.11.03
 */
class TbkDgVegasSendStatusRequest
{
	/** 
	 * 入参类型(该模式下返回的结果为模糊匹配结果，和实际情况可能存在误差)： 1. IMEI 2. IDFA 3. OAID 4. MOBILE 5. ALIPAY_ID
	 **/
	private $deviceType;
	
	/** 
	 * 加密后的值(ALIPAY_ID除外)，需用MD5加密，32位小写
	 **/
	private $deviceValue;
	
	/** 
	 * 渠道管理id
	 **/
	private $relationId;
	
	/** 
	 * 会员运营id
	 **/
	private $specialId;
	
	private $apiParas = array();
	
	public function setDeviceType($deviceType)
	{
		$this->deviceType = $deviceType;
		$this->apiParas["device_type"] = $deviceType;
	}

	public function getDeviceType()
	{
		return $this->deviceType;
	}

	public function setDeviceValue($deviceValue)
	{
		$this->deviceValue = $deviceValue;
		$this->apiParas["device_value"] = $deviceValue;
	}

	public function getDeviceValue()
	{
		return $this->deviceValue;
	}

	public function setRelationId($relationId)
	{
		$this->relationId = $relationId;
		$this->apiParas["relation_id"] = $relationId;
	}

	public function getRelationId()
	{
		return $this->relationId;
	}

	public function setSpecialId($specialId)
	{
		$this->specialId = $specialId;
		$this->apiParas["special_id"] = $specialId;
	}

	public function getSpecialId()
	{
		return $this->specialId;
	}

	public function getApiMethodName()
	{
		return "taobao.tbk.dg.vegas.send.status";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
