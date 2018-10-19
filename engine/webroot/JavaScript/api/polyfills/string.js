(function(){
	if(typeof String.prototype.ucfirst !== "function"){
		String.prototype.ucfirst=()=>{ return this.charAt(0).toUpperCase()+this.slice(1); }
	}

	if(typeof String.prototype.stripDiatrics !=="function"){
		let $arr="AAAAAAACEEEEIIIIDNOOOOO*OUUUUYIsaaaaaaaceeeeiiii?nooooo/ouuuuy?y" +
			"AaAaAaCcCcCcCcDdDdEeEeEeEeEeGgGgGgGgHhHhIiIiIiIiIiJjJjKkkLlLlLlLlLlNnNnNnnNnOoOo" +
			"OoOoRrRrRrSsSsSsSsTtTtTtUuUuUuUuUuUuWwYyYZzZzZzF";
		String.prototype.stripDiatrics=function(){
			let result = this.split('');
			for (let i = 0; i < result.length; i++) {
				let c = this.charCodeAt(i);
				if (c >= 0x00c0 && c <= 0x017f)
					result[i] = String.fromCharCode($arr.charCodeAt(c - 0x00c0));
				else if (c > 127) result[i] = '?';
			}
			return result.join('');
		}
	}

	if(typeof String.prototype.substrDelimit !== "function"){
		String.prototype.substrDelimit = function(start,length,delimiter){
			if(!start) start=0;
			if(!length) length=this.length;
			if(!delimiter) delimiter={ start : "@_START", end : "@_END" };
			else if(typeof delimiter === "string") delimiter={ start : delimiter, end : delimiter };
			else if(typeof delimiter === "object"){
				delimiter.start=((delimiter.start)?delimiter.start:"@_START");
				delimiter.end=((delimiter.end)?delimiter.end:"@_END");
			}
			let res=""; let sub=delimiter.start+this.substr(start,length)+delimiter.end;
			if(start === 0)res+=sub;
			else res+=this.substr(0,start)+sub;

			if(length<this.length) res+=this.substr(start+length);
			return res;
		}
	}
})();