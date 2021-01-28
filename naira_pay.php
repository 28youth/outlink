<?php

/**
 * 尼日利亚wow支付demo 
 */
class Pay
{

	// 商户测试 key
	const Key = '00e6f80665cff225';
	
	// 测试pin认证下单
	public function order_pin_test($reference)
    {
        $url = "http://tianxie.today/ngrpay/card/order_test";
        $data = [
            'merchantId' => '10000',
            'card_number' => '5531886652142950',
            'cvv' => '564',
            'expiry_month' => '09',
            'expiry_year' => '32',
            'currency' => 'NGN',
            'amount' => 100,
            'fullname' => 'Yemi Desola',
            'email' => 'user@vv.com',
            'reference' => $reference,
        ];

        $sign = $this->getSign(self::Key, $data);
        print_r($sign);exit;
        $params = array_merge($data, compact('sign'));

        return $this->curlPost($url, $params);
    }

    // 测试3d认证下单
	public function order_3d_test($reference)
    {
        $test = [
            'email' => 'zongjie.li@opay-inc.com',
            'key' => '00e6f80665cff225',
            // 'sign' => '',
        ];
        $sign = $this->getSign(self::Key, $test);

        echo "<pre> step1:";
        print_r($sign);exit;

        $url = "http://tianxie.today/ngrpay/card/order_test";
        $data = [
            'merchantId' => '10000',
            'card_number' => '5438898014560229',
            'cvv' => '564',
            'expiry_month' => '10',
            'expiry_year' => '31',
            'currency' => 'NGN',
            'amount' => 100,
            'fullname' => 'Yemi Desola',
            'email' => 'user@vv.com',
            'reference' => $reference,
        ];

        $sign = $this->getSign(self::Key, $data);

        $params = array_merge($data, compact('sign'));

        return $this->curlPost($url, $params);
    }

    // 测试pin支付接口
    public function charge_test($reference)
    {
        $url = "http://tianxie.today/ngrpay/card/charge_test";
        $data = [
            'merchantId' => '10000',
            'pin' => '3310',
            'reference' => $reference,
        ];

        $sign = $this->getSign(self::Key, $data);

        $params = array_merge($data, compact('sign'));

        return $this->curlPost($url, $params);
    }

    // 测试otp授权支付接口
    public function charge_auth_test($reference)
    {
        $url = "http://tianxie.today/ngrpay/card/authorize_test";
        $data = [
            'merchantId' => '10000',
            'otp' => '12345',
            'reference' => $reference,
        ];

        $sign = $this->getSign(self::Key, $data);

        $params = array_merge($data, compact('sign'));

        return $this->curlPost($url, $params);
    }

    /**
	 * 获取订单号
	 */
    public function randOrderNo()
    {    
	    return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
	}

    /**
	 * 获取签名
	 */
	public function getSign($key, $params)
	{
	    ksort($params);
	    $params = http_build_query($params) . "&key={$key}";

	    return strtoupper(md5($params));
	}

    public function curlPost($url, $data, $header = [])
    {
        $ch = curl_init();
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

}

class Test {
	public function test_pin()
	{
		$pay = new Pay();
		$orderNo = $pay->randOrderNo(); // 测试订单号

		try {
			// 万事达卡pin 三步支付流程
			$order = json_decode($pay->order_pin_test($orderNo), true); // 测试下单
			echo "<pre> step1:";
			print_r($order);
			if (!empty($order) && $order['statusCode'] == 01) {

				$pinRes = json_decode($pay->charge_test($orderNo), true); // 测试pin支付
				echo "<pre> step2:";
				print_r($pinRes);
				if (!empty($pinRes) && $pinRes['statusCode'] == 01) {

					$otpRes = json_decode($pay->charge_auth_test($orderNo), true); // 测试otp支付
					echo "<pre> step3:";
					print_r($otpRes);
				}
			}
		} catch (\Exception $e) {
			echo "<pre>";
			print_r($e->getMessage());
		}
	}

	public function test_3d()
	{
		$pay = new Pay();
		$orderNo = $pay->randOrderNo(); // 测试订单号

		try {
			// 万事达卡3d 支付流程
			$order = json_decode($pay->order_3d_test($orderNo), true); // 测试下单
			echo "<pre> step1:";
			print_r($order);

			if (!empty($order) && $order['statusCode'] == 01) {

				// 打开后输入 otp //12345 支付成功后会跳转到 下单接口时传入的redirect_url 并返回状态
				header("Location:".$order['data']['meta']['url']); 
			}
		} catch (\Exception $e) {
			echo "<pre>";
			print_r($e->getMessage());
		}
	}
}

$test = new Test();

// $test->test_pin(); // 测试pin认证

// $test->test_3d(); // 测试3d认证


$pay = new Pay();
$orderNo = 'MC-J247416109673433'; // 测试订单号
$order = json_decode($pay->order_pin_test($orderNo), true); // 测试下单
echo "<pre> :";
print_r($order);exit;






