wfw.require("api/network/ajax");
wfw.define("network/wfwAPI",(function($csrfToken){
	return function($url,$params){
		$params = ($params) ? $params : {};
		let $success = (typeof $params.success === 'function') ? $params.success : null;
		let $error = (typeof $params.error === 'function') ? $params.error : ()=>null;
		(typeof $params.getData === "object")
			? $params.getData.ajax=true : $params.getData={ajax:true};
		if($csrfToken && !('csrfToken' in $params.getData)) $params.getData.csrfToken = $csrfToken;

		$params.success = function($response){
			let $code = "-1"; let $res = null;
			try{
				let $resp = JSON.parse($response).response; $code = $resp.code; $res = $resp.text;
			}catch($err){
				if(!$error) throw new Error("Unreadable response : "+$response);
				else $error($response);
				return undefined;
			}
			if($code && typeof $params[$code] === 'function') $params[$code]($res);
			else if(typeof $code === "string" && $code.charAt(0) !== '0') $error($res,$code);
			else if($success) $success($res,$code);
		};
		wfw.network.ajax($url,$params);
	};
})(window.csrfToken || null));