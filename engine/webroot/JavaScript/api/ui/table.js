wfw.require('api/dom/nodeHelper');
wfw.define('ui/table',function($columns,$params){
	if(!Array.isArray($columns)) throw new Error("First arg have to be an array !");
	if($columns.length === 0) throw new Error("A table must contain at least one column !");

	$params = $params || {}; let $table = null, $head = null, $body = null, $inst = this;
	let $chk = ('checkboxes' in $params) ? $params.checkboxes : false; let $lastChecked;
	let $colsMap = new WeakMap(), $rowsMap = new WeakMap(), $cols = [], $rows = [];
	let $clcolsMap = new WeakMap(), $clcols = [];
	if('rowEvents' in $params){
		if(typeof $params.rowEvents!=="object") throw new Error("rowEvents have to be an object !");
	}else $params.rowEvents = {};

	let $colClick = function($e){
		let $col = $e.currentTarget; let $inv = ($s) => ($s==="asc") ? "desc" : "asc";
		let $sParams = $colsMap.get($col).sort;
		if($sParams.disabled) return;
		if($clcolsMap.has($col)){
			if($clcolsMap.get($col)){
				$clcolsMap.set($col,false); $col.classList.remove($sParams.first+'-sort');
				$col.classList.add($inv($sParams.first)+'-sort');
			}else{
				$clcolsMap.delete($col); $col.classList.remove($inv($sParams.first)+'-sort');
				$clcols = $clcols.filter(($c)=>{return $c!==$col;});
			}
		}else{
			$clcolsMap.set($col,true); $col.classList.add($sParams.first+"-sort");
			$clcols.push($col);
		}
		if(!$e.shiftKey && $clcols.length > 0){
			let $toRemove = [];
			$clcols.filter(($c)=>{return $c!==$col;}).forEach(($c)=>{
				if($c.classList.contains('desc-sort')) $c.classList.remove('desc-sort');
				else $c.classList.remove('asc-sort');
				$clcolsMap.delete($c);
				$toRemove.push($c);
			});
			$clcols = $clcols.filter(($c)=>{return $toRemove.indexOf($c) < 0;});
		}
		$sort();
	};
	let $prepareSort = ()=>{
		let $pos = new WeakMap();
		let $toSort = $rows.filter(($r,$i)=>{$pos.set($r,$i);return true;});
		return {fn:($a,$b) => {
			let $res = undefined; let $ad = $rowsMap.get($a); let $bd = $rowsMap.get($b);
			for(let $i =0; $i<$clcols.length; $i++){
				let $colIndex = $cols.indexOf($clcols[$i]);
				if(($res=$colsMap.get($clcols[$i]).comparator($ad[$colIndex],$bd[$colIndex]))!==0){
					if($clcols[$i].classList.contains("desc-sort")) $res *=-1; break;
				}
			}
			if($res===0){ $res = $pos.get($a)-$pos.get($b); }
			return $res;
		},toSort : $toSort};
	};
	let $sort = ()=>{
		let $base;
		if($clcols.length>0){ let $sort = $prepareSort(); $base = $sort.toSort.sort($sort.fn); }
		else $base = $rows;
		while($body.firstChild){$body.removeChild($body.firstChild);}
		$base.forEach(($r)=>{$body.appendChild($r)});
	};

	$table = wfw.dom.appendTo(wfw.dom.create("table"),
		$head = wfw.dom.create("thead"), $body = wfw.dom.create("tbody")
	);
	let $colRow = null; $head.appendChild($colRow = wfw.dom.create("tr"));

	if($chk) $colRow.appendChild(wfw.dom.create("th",{className:"checkboxe"}));
	$columns.forEach(($c,$i)=>{
		if(typeof $c !== "object") throw new Error(`Unexpected column value at index ${$i}`);
		if(!('name' in $c)) throw new Error(`Col ${$i} doesn't have a 'name' property !`);
		if('displayer' in $c){
			if(typeof $c.displayer !== 'function')
				throw new Error(`Col ${$i} : diplayer have to be a function !`);
		}else $c.displayer = ($val) => {return $val; };
		if('comparator' in $c){
			if(typeof $c.comparator !== 'function')
				throw new Error(`Col ${$i} : comparator have to be a function !`);
		}else $c.comparator = ($v1,$v2) => {
			if($v1<$v2) return -1; else if($v1>$v2) return 1; else return 0;
		};
		if('cellEvents' in $c){
			if(typeof $c !== "object")
				throw new Error(`Col ${$i} : cellEvents have to be an object !`);
		}else $c.cellEvents = {};
		if('sort' in $c){
			if(typeof $c.sort !== "object")
				throw new Error(`Col ${$i} : sort have to be an object !`);
			if("default" in $c.sort){
				if([null,"asc","desc"].indexOf($c.sort.default) < 0)
					throw new Error(`Col : ${$i} : sort.default must be null,asc,desc`);
			}else $c.sort.default = null;
			if("first" in $c.sort){
				if([null,"asc","desc"].indexOf($c.sort.first) < 0)
					throw new Error(`Col : ${$i} : sort.first must be null,asc,desc`);
			}else $c.sort.first = "asc";
			if(!("disabled" in $c.sort)) $c.sort.disabled = false;
		}else $c.sort = {default:null,first:"asc",disabled:false};

		let $el; wfw.dom.appendTo($colRow,
			$el = wfw.dom.create("th",{innerHTML:$c.name,on:{click:$colClick}})
		);
		$colsMap.set($el,$c); $cols.push($el);
		if($c.sort.default !== null)
			$el.dispatchEvent(new MouseEvent("click",{bubbles:true,shiftKey:true}));
	});

	let $getRowIndex = ($r)=>{let $res=0; while($r=$r.previousElementSibling){$res++;}return $res;};
	let $checkHelper = ($e)=>{
		$e.stopPropagation();
		if($e.target.firstChild) $e.target.firstChild.dispatchEvent(new MouseEvent("click",{
			bubbles : true, shiftKey : $e.shiftKey
		})); else{
			if(!$lastChecked) $lastChecked = $e.target; else{
				if($e.shiftKey){
					let $row = $e.target.parentNode.parentNode;let $first; let $last;
					let $currentIndex = $getRowIndex($row);
					let $lastIndex = $getRowIndex($lastChecked.parentNode.parentNode);
					if($lastIndex > $currentIndex){ $first = $currentIndex; $last = $lastIndex; }
					else{ $first = $lastIndex; $last = $currentIndex; }
					$body.querySelectorAll(
						`tr:nth-child(n+${$first+1}):nth-child(-n+${$last}) .checkboxe > input`
					).forEach(($c)=>{$c.checked = $e.target.checked});
				}
				$lastChecked = $e.target;
			}
		}
	};
	let $get = ($i) => ($body.querySelectorAll("tr")[$i]);
	let $getRowData = ($r) => ($rowsMap.get($r));
	let $createRow = (...$data) => {
		let $row = wfw.dom.create("tr");
		Object.keys($params.rowEvents).forEach(($e)=>$row.addEventListener($e,$params.rowEvents[$e]));
		if($chk) $row.appendChild(wfw.dom.appendTo(
			wfw.dom.create('td',{className:"checkboxe",on:{click:$checkHelper}}),
			wfw.dom.create('input',{type:"checkbox"})
		));
		$cols.forEach(($col,$i)=>{
			let $d = ($i<$data.length) ? $data[$i] : undefined;
			$row.appendChild(wfw.dom.create('td',
				{innerHTML:$colsMap.get($col).displayer($d),on:$colsMap.get($col).cellEvents}
			));
		});
		return $row;
	};
	let $accordRowToCurrentSort = ($row,$edited)=>{
		let $sort = $prepareSort(); let $inserted = false;
		let $trs = Array.from($body.querySelectorAll("tr")).filter(($r)=>($r!==$row));
		for(let $i =0; $i<$trs.length; $i++){
			let $res = $sort.fn($row,$trs[$i]);
			if($res <= 0){ $body.insertBefore($row,$trs[$i]); $inserted = true; break;}
		}
		if(!$inserted && !$edited){ $body.appendChild($row); }
	};
	let $addRow = (...$data) => {
		let $row = $createRow(...$data); $rowsMap.set($row,$data); $rows.push($row);
		$accordRowToCurrentSort($row);
	};
	let $editRow = ($row,$data)=>{
		let $cel = $row.querySelectorAll("td:not(.checkboxe)"); let $rowData = $rowsMap.get($row);
		$cols.forEach(($col,$i)=>{
			let $d = ($i in $data) ? $data[$i] : undefined;
			$d = ($colsMap.get($col).name in $data) ? $data[$colsMap.get($col).name] : $d;
			if(typeof $d !== "undefined"){
				let $res = $colsMap.get($col).displayer($d);
				if(typeof $res !== "object") $cel[$i].innerHTML = $res;
				else { $cel[$i].innerHTML = ''; $cel[$i].appendChild($res); }
				$rowData[$i] = $d;
			}
		});
		if(Array.isArray($data))
			$data.slice($cols.length).forEach(($d,$i)=>{$rowData[$i+$cols.length]=$d});
		$rowsMap.set($row,$rowData); $accordRowToCurrentSort($row,true);
	};
	let $removeRow = ($row) => {
		if(!$rowsMap.has($row)) throw new Error("Row not found");
		$row.parentNode.removeChild($row); $rowsMap.delete($row);
		$rows = $rows.filter(($r)=>$r!==$row);
	};
	let $removeRowAt = ($i) => {
		if(!Number.isInteger($i)) throw new Error("First arg have to be an integer");
		if($i>=$rows.length || $i<0) throw new Error("Out of bounds");
		$removeRow($body.querySelector(`tr:nth-child(${$i+1})`));
	};
	let $removeRows = ($from, $len) => {
		if(!Number.isInteger($from)) throw new Error("First arg have to be an integer");
		if($from>=$rows.length || $from<0) throw new Error("$from is out of bounds");
		$len = Number.isInteger($len) ? $len : $rows.length - $from; let $toDelete=[];
		for(let $i = $from; $i<$len; $i++){ $toDelete.push($rows[$i]); }
		$toDelete.forEach(($r)=>$removeRow($r));
	};
	let $getSelectedRows = ($data)=>{
		return Array.from($body.querySelectorAll("input:checked")).map(($e)=>{
			if($data) return $getRowData($e.parentNode.parentNode);
			else return $e.parentNode.parentNode;
		});
	};
	let $redefineError = () => {throw new Error("Can't redefine table properties !")};
	Object.defineProperties($table,{
		wfw : { get : ()=> $inst, set : $redefineError }
	});
	Object.defineProperties(this,{
		get : { get : () => $get, set : $redefineError},
		head : { get : () => $head, set : $redefineError},
		body : { get : () => $body, set : $redefineError},
		rows : { get : () => $rows.map($r=>$r), set : $redefineError },
		html : { get : () => $table, set : $redefineError},
		addRow : { get : () => $addRow, set : $redefineError},
		editRow : { get : () => $editRow, set : $redefineError},
		removeRow : { get :() => $removeRow, set : $redefineError},
		removeRows : { get :() => $removeRows, set : $redefineError},
		removeRowAt : { get :() => $removeRowAt, set : $redefineError},
		getRowData : { get : () => $getRowData, set : $redefineError },
		getRowIndex : { get : () => $getRowIndex, set : $redefineError },
		getSelectedRows : {get : ()=> $getSelectedRows, set : $redefineError}
	});
});