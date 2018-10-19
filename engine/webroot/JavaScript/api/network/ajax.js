wfw.define("network/ajax",(function(){
	let $getData = function($d,$append){
		let $res = (!$append) ? '?' : '&';
		$res += Object.keys($d)
			.filter(($k) => { return $d[$k] !== undefined; })
			.map(($k) => { return encodeURIComponent($k) + '=' + encodeURIComponent($d[$k]); })
			.join('&');
		return ($res.length>1) ? $res : '';
	};
	let $postData = function($d,$formData){
		if($d && $d instanceof FormData) return $d;
		$d = ($d && typeof $d === "object") ? $d : {};
		$formData = ($formData instanceof FormData) ? $formData : new FormData();
		Object.keys($d)
			.filter(($key)=>{return $d[$key] !== undefined && $d[$key] !== null})
			.forEach(($k) => {
				if($d[$k] instanceof File) $formData.append($k,$d[$k]);
				else if(Array.isArray($d[$k])) $d[$k].forEach(($elem) => {
					$formData.append($k,typeof $elem === "object" ? JSON.stringify($elem) : $elem);
				});
				else if(typeof $d[$k] === "object") $formData.append($k,JSON.stringify($d[$k]));
				else $formData.append($k,$d[$k]);
		});
		return $formData;
	};
	return function($url,$params){
		let $req = {};
		$req.type = ('type' in $params) ? $params.type.toUpperCase() : "GET";
		$req.headers = ('headers' in $params) ? $params.headers : {};
		$req.withCredentials = ('withCredentials' in $params) ? $params.withCredentials : false;
		$req.appendGetData = ('appendGetData' in $params) ? $params.appendGetData : false;
		$req.appendPostData = ('appendPostParams' in $params) ? $params.appendPostData : false;
		//Data
		$req.data = ('data' in $params) ? $params.data : null;
		$req.getData = ('getData' in $params) ? $params.getData : {};
		$req.getData = $getData($req.getData,$req.appendGetData);
		$req.postData = ('postData' in $params) ? $params.postData : null;
		if($req.appendPostData) $req.postData = $postData($req.postData,$req.appendPostData);
		else $req.postData = $postData($req.postData);
		//Callbacks
		$req.error = (typeof $params.error === 'function') ? $params.error : null;
		$req.onload = (typeof $params.onload === 'function') ? $params.onload : null;
		$req.success = (typeof $params.success === 'function') ? $params.success : null;
		$req.beforeSend = (typeof $params.beforeSend === 'function') ? $params.beforeSend : null;
		$req.onreadystatechange = (typeof $params.onreadystatechange === 'function')
			? $params.onreadystatechange : null;

		let $xhr = new XMLHttpRequest();
		if($req.onreadystatechange) $xhr.onreadystatechange = $req.onreadystatechange;
		$xhr.open($req.type,$url+$req.getData);
		Object.keys($req.headers).forEach(($key) => $xhr.setRequestHeader($key,$req.headers[$key]));
		$xhr.onload = ($req.onload) ? $req.onload($xhr) : function(){
			if($xhr.status >= 200 && $xhr.status < 400){
				if($req.success) $req.success($xhr.responseText,$xhr);
			}else{ if($req.error) $req.error($xhr.responseText,$xhr); }
		};
		$xhr.onerror = ()=>{ if($req.error) $req.error($xhr.responseText,$xhr); };
		if($req.beforeSend) $req.beforeSend($xhr);
		if($req.type !== 'GET') $xhr.send($req.postData ? $req.postData : $req.data);
		else $xhr.send();
	};
})());