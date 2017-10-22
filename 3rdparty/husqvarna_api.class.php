<?php
class husqvarna_api {

	protected $url_api_im = 'https://iam-api.dss.husqvarnagroup.net/api/v3/';
	protected $url_api_track = 'https://amc-api.dss.husqvarnagroup.net/v1/';
	protected $username;
	protected $password;
	protected $token;
	protected $provider;

    function login($username, $password)
	{
        $this->username = $username;
        $this->password = $password;
		$fields["data"]["attributes"]["username"] = $this->username;
		$fields["data"]["attributes"]["password"] = $this->password;
		$fields["data"]["type"] = "token";
		$result = $this->post_api("token", $fields);
		if ( $result !== false )
		{
			$this->token = $result->data->id;
			$this->provider = $result->data->attributes->provider;
			return true;
		}
		return false;
	}

	private function get_headers($fields = null)
	{
		if ( isset($this->token) )
		{
			$generique_headers = array(
			   'Content-type: application/json',
			   'Accept: application/json',
				'Authorization: Bearer '.$this->token,
				'Authorization-Provider: '.$this->provider
			);
		}
		else
		{
			$generique_headers = array(
			   'Content-type: application/json',
			   'Accept: application/json'
			   );
		}
		if ( isset($fields) )
		{
			$custom_headers = array('Content-Length: '.strlen(json_encode ($fields)));
		}
		else
		{
			$custom_headers = array();
		}
		return array_merge($generique_headers, $custom_headers);
	}

	private function post_api($page, $fields = null)
	{
		$session = curl_init();

		curl_setopt($session, CURLOPT_URL, $this->url_api_im . $page);
		curl_setopt($session, CURLOPT_HTTPHEADER, $this->get_headers($fields));
		curl_setopt($session, CURLOPT_POST, true);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		if ( isset($fields) )
		{
			curl_setopt($session, CURLOPT_POSTFIELDS, json_encode ($fields));
		}
		$json = curl_exec($session);
		curl_close($session);
//		throw new Exception(__('La livebox ne repond pas a la demande de cookie.', __FILE__));
		return json_decode($json);
	}

	private function get_api($page, $fields = null)
	{
		$session = curl_init();

		curl_setopt($session, CURLOPT_URL, $this->url_api_track . $page);
		curl_setopt($session, CURLOPT_HTTPHEADER, $this->get_headers($fields));
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		if ( isset($fields) )
		{
			curl_setopt($session, CURLOPT_POSTFIELDS, json_encode($fields));
		}
		$json = curl_exec($session);
		curl_close($session);
//		throw new Exception(__('La livebox ne repond pas a la demande de cookie.', __FILE__));
		return json_decode($json);
	}

	private function del_api($page)
	{
		$session = curl_init();

		curl_setopt($session, CURLOPT_URL, $this->url_api_im . $page);
		curl_setopt($session, CURLOPT_HTTPHEADER, $this->get_headers());
		curl_setopt($session, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		$json = curl_exec($session);
		curl_close($session);
//		throw new Exception(__('La livebox ne repond pas a la demande de cookie.', __FILE__));
		return json_decode($json);
	}

    function logout()
	{
		$result = $this->del_api("token/".$this->token);
		if ( $result !== false )
		{
			unset($this->token);
			unset($this->provider);
			return true;
		}
		return false;
	}
	
	function list_robots()
	{
		$list_robot = array();
		foreach ($this->get_api("mowers") as $robot)
		{
			$list_robot[$robot->id] = $robot;
		}
		return $list_robot;
	}

	function get_status($mover_id)
	{
		
		return $this->get_api("mowers/".$mover_id."/status");
	}

	function get_geofence($mover_id)
	{
		
		return $this->get_api("mowers/".$mover_id."/geofence");
	}

	function control($mover_id, $command)
	{
		if ( in_array($command, array('PARK', 'STOP', 'START') ) )
		{
			return $this->get_api("mowers/".$mover_id."/control", array("action" => $command));
		}
	}
}
?>