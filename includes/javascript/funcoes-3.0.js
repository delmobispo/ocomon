	var GLArray = [];

	function popup(pagina)	{ //Exibe uma janela popUP
		x = window.open(pagina,'Gráfico','dependent=yes,width=800,height=600,scrollbars=yes,statusbar=no,resizable=no');
		x.moveTo(10,10);

		return false;
	}

	function popupS(pagina)	{ //Exibe uma janela popUP
		x = window.open(pagina,'Gráfico','dependent=yes,width=800,height=600,scrollbars=yes,statusbar=no,resizable=no');
		x.moveTo(10,10);

		return false;
	}

	function popupWH(pagina,larg,altur)	{ //Exibe uma janela popUP
		x = window.open(pagina,'Gráfico','dependent=yes,width='+(larg+20)+',height='+(altur+20)+',scrollbars=no,statusbar=no,resizable=no');
		x.moveTo(10,10);

		return false;
	}


	function popup_alerta(pagina)	{ //Exibe uma janela popUP
		x = window.open(pagina,'_blank','dependent=yes,width=600,height=400,scrollbars=yes,statusbar=no,resizable=yes');

		x.moveTo(window.parent.screenX+50, window.parent.screenY+50);
		return false;
	}

	function popup_wide(pagina)	{ //Exibe uma janela popUP
		x = window.open(pagina,'_blank','dependent=yes,width=600,height=200,scrollbars=yes,statusbar=no,resizable=yes');

		x.moveTo(window.parent.screenX+50, window.parent.screenY+50);
		return false;
	}

	function mini_popup(pagina)	{ //Exibe uma janela popUP
		x = window.open(pagina,'_blank','dependent=yes,width=400,height=250,scrollbars=yes,statusbar=no,resizable=yes');
		x.moveTo(window.parent.screenX+50, window.parent.screenY+50);
		return false;
	}

	function popup_alerta_mini(pagina)	{ //Exibe uma janela popUP
		x=window.open(pagina,'_blank','dependent=yes,width=400,height=250,scrollbars=yes,statusbar=no,resizable=yes');
		x.moveTo(100,100);
		x.moveTo(window.parent.screenX+50, window.parent.screenY+50);
		return false;
	}


	function popup_alerta_wide(pagina)	{ //Exibe uma janela popUP
		x = window.open(pagina,'_blank','dependent=yes,width=800,height=400,scrollbars=yes,statusbar=no,resizable=yes');
		x.moveTo(window.parent.screenX+50, window.parent.screenY+50);
		return false;
	}


	function mensagem(msg){
		alert(msg);
		return false;
	}


	function redirect(url){
		window.location.href=url;
	}

	function redirectLoad(url, id){
		var obj = document.getElementById(id);
		window.location.href=url+obj.value;
	}

	function submitForm (obj) {
		obj.form.submit();
	}

	function reloadUrl(url, param){
		var obj = document.getElementById(id);
		window.location.href=url+param;
	}

	//criar acesso ao submit de excluir
	function confirma(msg,url){
		if (confirm(msg)){
			redirect(url);
		}
	}


	function confirmaAcao (msg, url, param){ //variavel php
		if (confirm(msg)){
			url += '?'+param;
			redirect(url);
		}
		return false;
	}


	function cancelLink () {
		return false;
	}

	function disableLink (link) {
		if (link.onclick)
			link.oldOnClick = link.onclick;
		link.onclick = cancelLink;
		if (link.style)
			link.style.cursor = 'default';
	}

	function enableLink (link) {
		link.onclick = link.oldOnClick ? link.oldOnClick : null;
		if (link.style)
			link.style.cursor =
			document.all ? 'hand' : 'pointer';
	}
	function toggleLink (link) {
	  if (link.disabled)
		enableLink (link)
	  else
		disableLink (link);
	  link.disabled = !link.disabled;
	}

	function desabilitaLinks(permissao){
		if (permissao!=1) {
			for (i=0; i<(document.links.length); i++) {
				toggleLink (document.links[i]);
			}
		}
	}

	function par(n) {
		var na = n;
		var nb = (na / 2);
		nb = Math.floor(nb);
		nb = nb * 2;
		if ( na == nb ) {
			return(1);
		} else {
			return(0);
		}
	}


	function corNatural(id) {//F8F8F1
		var obj = document.getElementById(id);

		var args = corNatural.arguments.length;
		//var id = destaca.arguments[0];
		if (args==1){
			//var color = "#CCCCFF";
			var color = "";
		} else
		if (args == 2)
			var color = corNatural.arguments[1];
		else
		if (args == 3){
			var color = corNatural.arguments[1];
			var color2 = corNatural.arguments[2];
		}

		if (navigator.userAgent.indexOf('MSIE') !=-1){ //M$ IE
			var classe = obj.getAttributeNode('class').value;
			obj.style.background = color;
			//var classe = obj.className;
		} else {
			//var classe ='';
			var classe = obj.getAttributeNode('class').value;
		}

		if ( classe != '') {

			if ( classe == 'lin_par'  ) {  obj.style.background = color;  } else
			if ( classe == 'lin_impar' ) { obj.style.background = color2 ;}

		}
		else { obj.style.background = color; }
	}

		function listItems()
		{
			var items = listItems.arguments.length
			document.write("<UL>\n")
			for (i = 0;i < items;i++)
			{
				document.write("<LI>" + listItems.arguments[i] + "\n")
			}
			document.write("</UL>\n")
		}

		function setBGColor(id){
			var obj = document.getElementById(id);

			if (obj.value!="IMG_DEFAULT")
				obj.style.background="";
			obj.style.backgroundColor = obj.value;

			return false;
		}

		function destaca(){

			var args = destaca.arguments.length;
			var id = destaca.arguments[0];

			if (args==1){
				//var color = "#CCCCFF";
				var color = "";
			} else
				var color = destaca.arguments[1];

			if ( verificaArray('', id) == false ) {
				var obj = document.getElementById(id);
				//obj.style.background = '#CCCCFF';// #CCFFCC #C7C8C6 #A3A352 '#D5D5D5'  #CCFFCC   #FDFED8
				obj.style.background = color;
			}
		}

		function libera(id){

			var args = libera.arguments.length;
			//var id = destaca.arguments[0];
			if (args==1){
				var color = "";
			} else
			if (args == 2)
			{
				var color = libera.arguments[1];
			} else
			if (args == 3) {
				var color = libera.arguments[1];
				var color2 = libera.arguments[2];
			} else
				var color2 = '';


			if ( verificaArray('', id) == false ) {
				var obj = document.getElementById(id);
				//obj.style.background = '';
				corNatural(id,color,color2); /* retorna à cor natural */
			}
		}


		function marca(){
			var args = marca.arguments.length;
			var id = marca.arguments[0];

			var obj = document.getElementById(id);
			if (args==1){
				//var color = "#FFCC99";
				var color = "";
			} else
				var color = marca.arguments[1];


			if ( verificaArray('', id) == false ) {
				verificaArray('marca', id)

				//obj.style.background = '#FFCC99';
				obj.style.background = color;
			} else {
				verificaArray('desmarca', id)
				//obj.style.background = '';
				destaca(id);
			}

		}

		function verificaArray(acao, id) {
			var i;
			var tamArray = GLArray.length;
			var existe = false;

			for(i=0; i<tamArray; i++) {
				if ( GLArray[i] == id ) {
					existe = true;
					break;
				}
			}

			if ( (acao == 'marca') && (existe==false) ) {
				GLArray[tamArray] = id;
			} else if ( (acao == 'desmarca') && (existe==true) ) {
				var temp = new Array(tamArray-1); //-1
				var pos = 0;
				for(i=0; i<tamArray; i++) {
					if ( GLArray[i] != id ) {
						temp[pos] = GLArray[i];
						pos++;
					}
				}

				GLArray = new Array();
				var pos = temp.length;
				for(i=0; i<pos; i++) {
					GLArray[i] = temp[i];
				}
			}

			return existe;
		}

	function loadDefaultValue(id, valor){
		var obj = document.getElementById(id);
		obj.value = valor;
		return false;
	}


function validaForm(id,tipo,campo,obrigatorio){
	
	var texto = "";
	var args = validaForm.arguments.length;
        
	if (args==5){//um quinto parametro foi passado
		texto = validaForm.arguments[4];
	}
	
	var regINT = /^[1-9]\d*$/; //expressão para validar numeros inteiros não iniciados com zero
	var regCOMBO = /^[0-9]\d*$/; //expressão para validar numeros inteiros incluindo iniciados por zero
	var regCOMBOSTRING = /^(?!\-1).*$/; //expressão para validar qualquer coisa nao iniciada por '-1'
    var regANO = /^\d{4}$/; //expressão para validar valor numerico com 4 algarismos
    
    var regINTFULL = /^\d*$/; //expressão para validar numeros inteiros quaisquer
	var regDATA = /^((0?[1-9]|[12]\d)\/(0?[1-9]|1[0-2])|30\/(0?[13-9]|1[0-2])|31\/(0?[13578]|1[02]))\/(19|20)?\d{2}$/;
	var regDATA_ = /^((0?[1-9]|[12]\d)\-(0?[1-9]|1[0-2])|30\-(0?[13-9]|1[0-2])|31\-(0?[13578]|1[02]))\-(19|20)?\d{2}$/;
	var regHora = /^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$/;
	var regDATAHORA = /^(((0?[1-9]|[12]\d)\/(0?[1-9]|1[0-2])|30\/(0?[13-9]|1[0-2])|31\/(0?[13578]|1[02]))\/(19|20)?\d{2})[ ]([0-1]\d|2[0-3])+:[0-5]\d:[0-5]\d$/;
	var regEMAIL = /^[\w!#$%&'*+\/=?^`{|}~-]+(\.[\w!#$%&'*+\/=?^`{|}~-]+)*@(([\w-]+\.)+[A-Za-z]{2,6}|\[\d{1,3}(\.\d{1,3}){3}\])$/;

	var regMULTIEMAIL = /^([\w!#$%&'*+\/=?^`{|}~-]+(\.[\w!#$%&'*+\/=?^`{|}~-]+)*@(([\w-]+\.)+[A-Za-z]{2,6}|\[\d{1,3}(\.\d{1,3}){3}\]))(\,\s?([\w!#$%&'*+\/=?^`{|}~-]+(\.[\w!#$%&'*+\/=?^`{|}~-]+)*@(([\w-]+\.)+[A-Za-z]{2,6}|\[\d{1,3}(\.\d{1,3}){3}\]))+)*$/;

	var regMOEDA = /^\d{1,3}(\.\d{3})*\,\d{2}$/;
	var regMOEDASIMP = /^\d*\,\d{2}$/;
    
    var regVALOR_DUAS_CASAS = /^(?:\d+(?:(\,|\.)\d{1,2})?)$/; //ACEITA VIRGULA OU PONTO
    
	var regETIQUETA = /^[1-9]\d*(\,\d+)*$/; //expressão para validar consultas separadas por vírgula;
	var regALFA = /^[A-Z]|[a-z]([A-Z]|[a-z])*$/;
	var regALFANUM = /^([A-Z]|[a-z]|[0-9])*\.?([A-Z]|[a-z]|[0-9])*$/; //Valores alfanumérias aceitando separação com no máximo um ponto.
	var regALFAFULL = /^[\w!#$%&'*+\/=?^`{|}~-]+(\.[\w!#$%&'*+\/=?^`{|}~-]+)*$/;
	var regALFAFULLESPACO = /^.*$/;
	var regUSUARIO = /^([0-9a-zA-Z]+([_.-]?[0-9a-zA-Z]+))$/;
    
    var regCNPJ = /^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$/;
	
	var regFoneAntigo = /^(([+][\d]{2,2})?([-]|[\s])?[\d]*([-]|[\s])?[\d]+)+([,][\s]([+][\d]{2,2})?([-]|[\s])?[\d]*([-]|[\s])?[\d]+)*$/;
	var regFone = /^(((\(\d{2}\)|(\d{2}))[\s-]?\d{4}[-|\.|\s]?\d{4,5})|((((\(\d{2}\)|(\d{2}))[\s-]?\d{4}[-|\.|\s]?\d{4,5})((\,\s)|(\s))?)+((\(\d{2}\)|(\d{2}))[\s-]?\d{4}[-|\.\s]?\d{4,5})))$/;
	
	var regCEP = /^\d{8}$/;
	
	var regUM = /^[1]$/;
	var regZERO = /^[0]$/;
	
	var regCor = /^([#]([A-F]|[a-f]|[\d]){6,6})|([I][M][G][_][D][E][F][A][U][L][T])$/;

	var obj = document.getElementById(id);
	var valor = obj.getAttribute('name').value;


	if ((obj.value == "")&&(obrigatorio==1)){
		alert("O campo [" + campo + "] deve ser preenchido!");
		obj.focus();
		return false;
	}

	if ((tipo == "INTEIRO")&&(obj.value != "")) {
		//validar dados numéricos
		if (!regINT.test(obj.value)){
			alert ("O campo ["+ campo +"] deve conter apenas numeros inteiros não iniciados por ZERO!");
			obj.focus();
			return false;
		}
	} else

	if ((tipo == "COMBO")&&(obj.value != "")) {
		//validar dados numéricos
		//alert (obj.value);
		if (!regCOMBO.test(obj.value)){
			alert ("O campo ["+ campo +"] deve ser selecionado!");
			//alert (obj.style.backgroundColor);
                        //obj.style.backgroundColor = "red";
                        obj.focus();
			return false;
		}
	} else

	if ((tipo == "COMBOSTRING")&&(obj.value != "")) {
		//validar dados numéricos
		//alert (obj.value);
		if (!regCOMBOSTRING.test(obj.value)){
			alert ("O campo ["+ campo +"] deve ser selecionado!");
                        //obj.style.backgroundColor = "red";
			obj.focus();
			return false;
		}
	} else


	if ((tipo == "ANO")&&(obj.value != "")) {
		//validar dados numéricos
		//alert (obj.value);
		if (!regANO.test(obj.value)){
			alert ("O campo ["+ campo +"] deve ser selecionado!");
                        //obj.style.backgroundColor = "red";
			obj.focus();
			return false;
		}
	} else
            
    if ((tipo == "INTEIROFULL")&&(obj.value != "")) {
		//validar dados numéricos
		if (!regINTFULL.test(obj.value)){
			alert ("O campo ["+ campo +"] deve conter apenas numeros inteiros!");
			obj.focus();
			return false;
		}
	} else

	if ((tipo == "DATA")&&(obj.value != "")) {
		//validar data
		if (!regDATA.test(obj.value)){
			alert("Formato de data invalido! dd/mm/aaaa");
			obj.focus();
			return false;
			}
	} else

	if ((tipo == "DATA-")&&(obj.value != "")) {
		//validar data
		if (!regDATA_.test(obj.value)){
			alert("Formato de data invalido! dd-mm-aaaa");
			obj.focus();
			return false;
			}
	} else

	if ((tipo == "HORA")&&(obj.value != "")) {
		//validar data
		if (!regHora.test(obj.value)){
			alert("Selecione a hora!");
			obj.focus();
			return false;
			}
	} else
	
	if ((tipo == "DATAHORA")&&(obj.value != "")) {
		//validar data
		if (!regDATAHORA.test(obj.value)){
			alert("Formato de data invalido! dd/mm/aaaa HH:mm:ss");
			obj.focus();
			return false;
			}
	} else

	if ((tipo == "EMAIL")&&(obj.value != "")){
		//validar email(verificao de endereco eletrônico)
		if (!regEMAIL.test(obj.value)){
			alert("Formato de e-mail invalido!");
			obj.focus();
			return false;
		}
	} else

	if ((tipo == "MULTIEMAIL")&&(obj.value != "")){
		//validar email(verificao de endereco eletrônico)
		if (!regMULTIEMAIL.test(obj.value)){
			
                    if (texto == "")
                        alert("Formato de e-mail invalido! \"E-MAIL, E-MAIL\"");
                    else
                        alert(texto);
                    
                    obj.focus();
                    return false;
		}
	} else

	if ((tipo == "MOEDA")&&(obj.value != "")){
		//validar valor monetário
		if (!regMOEDA.test(obj.value)){
			alert("Formato de moeda invalido!");
			obj.focus();
			return false;
		}
	} else

	if ((tipo == "MOEDASIMP")&&(obj.value != "")){
		//validar valor monetário
		if (!regMOEDASIMP.test(obj.value)){
			alert("Formato de moeda invalido! XXXXXX,XX");
			obj.focus();
			return false;
		}
	} else

	if ((tipo == "VALOR_DUAS_CASAS")&&(obj.value != "")){
		//validar valor monetário
		if (!regVALOR_DUAS_CASAS.test(obj.value)){
			alert ("O campo ["+ campo +"] deve ter o formato xx,xx!");
			obj.focus();
			return false;
		}
	} else

	if ((tipo == "ETIQUETA")&&(obj.value != "")){
		//validar valor monetário
		if (!regETIQUETA.test(obj.value)){
			alert("o Formato deve ser de valores inteiros nao iniciados por Zero e separados por virgula!");
			obj.focus();
			return false;
		}
	} else
        
        if ((tipo == "UM")&&(obj.value != "")){
		//validar valor monetário
		if (!regUM.test(obj.value)){
			
			if (texto == "")
				alert("VALOR NAO DEFINIDO - UM");
			else
				alert(texto);
		
			obj.focus();
			return false;
		}
	} else

        if ((tipo == "ZERO")&&(obj.value != "")){
		//validar valor monetário
		if (!regZERO.test(obj.value)){
			
			if (texto == "")
                alert("Ja existe pelo menos um chamado em aberto para essa mesma placa e o mesmo nao e compativel com essa solicitacao");
			else
                alert(texto);
                        
            obj.focus();
			return false;
		}
	}else

        if ((tipo == "ALFA")&&(obj.value != "")){
		//validar valor monetário
		if (!regALFA.test(obj.value)){
			alert("Esse campo so aceita carateres do alfabeto sem espaços!");
			obj.focus();
			return false;
		}
	}	else

	if ((tipo == "ALFANUM")&&(obj.value != "")){
		//validar valor monetário
		if (!regALFANUM.test(obj.value)){
			alert("Esse campo so aceita valores alfanumericos sem espaços ou separados por um ponto(no maximo um)!");
			obj.focus();
			return false;
		}
	}

	if ((tipo == "ALFAFULLESPACO")&&(obj.value != "")){
		//validar valor monetário
		if (!regALFAFULLESPACO.test(obj.value)){
			alert("Esse campo nao aceita esse tipo de valor!");
			obj.focus();
			return false;
		}
	}

	if ((tipo == "USUARIO")&&(obj.value != "")){
		//validar valor monetário
		if (!regUSUARIO.test(obj.value)){
			// alert("Formato de nome de usuario invalido!!");
			obj.focus();
			return false;
		}
	}
	
	if ((tipo == "CNPJ")&&(obj.value != "")){
		//validar valor monetário
		if (!regCNPJ.test(obj.value)){
			alert("Formato de CNPJ incorreto!");
			obj.focus();
			return false;
		}
	}
	
	if ((tipo == "ALFAFULL")&&(obj.value != "")){
		//validar valor monetário
		if (!regALFAFULL.test(obj.value)){
			alert("Esse campo so aceita valores alfanumericos sem espacos!");
			obj.focus();
			return false;
		}
	}

	if ((tipo == "FONEANTIGO")&&(obj.value != "")){
		//validar valor monetário
		if (!regFoneAntigo.test(obj.value)){
			alert("Esse campo so aceita valores formatados para telefones (algarismos, tracos e espacos) separados por virgula.");
			obj.focus();
			return false;
		}
	}
	
	
	if ((tipo == "FONE")&&(obj.value != "")){
		//validar valor monetário
		if (!regFone.test(obj.value)){
			alert("Esse campo so aceita valores formatados para telefones do sistema brasileiro ((xx) xxxx-xxxxx) separados por virgula e ou espaco.");
			obj.focus();
			return false;
		}
	}
	
	
	if ((tipo == "FONEFULL")&&(obj.value != "")){
		//validar valor monetário
		if (!regFoneAntigo.test(obj.value) && !regFone.test(obj.value) ){
			alert("Esse campo so aceita valores formatados para telefones do sistema brasileiro ((xx) xxxx-xxxxx) OU ramais, separados por virgula e ou espaco.");
			obj.focus();
			return false;
		}
	}

	
	if ((tipo == "CEP")&&(obj.value != "")){
		//validar CEP - apenas 8 algarismos
		if (!regCEP.test(obj.value)){
			alert("O CEP deve ser composto de 8 algarismos!");
			//alertBox("FORMATO INVALIDO!", "O CEP deve ser composto de 8 algarismos!","");
			obj.focus();
			return false;
		}
	}	
	
	
	if ((tipo == "COR")&&(obj.value != "")){
		//validar valor monetário
		if (!regCor.test(obj.value)){
			alert("Esse campo so aceita valores formatados para cores HTML! Ex: #FFCC99");
			obj.focus();
			return false;
		}
	}


	return true;
}

	function exibeEscondeImg(obj) {
		var item = document.getElementById(obj);
		if (item.style.display=='none'){
			item.style.display='';
		} else {
			item.style.display='none';
		}
	}

	function exibeEscondeHnt(obj) {

/*		if (document.all) {
			document.this.x.value=window.event.clientX;
			document.this.y.value=window.event.clientY;
		}
		else if (document.layers) {
			document.this.x.value=e.pageX;
			document.this.y.value=e.pageY;
		}*/


		if (document.all) {
			var x = window.event.clientX;
			var y = window.event.clientY;
		} else if (document.layers) {
			var x = pageX;
			var y = pageY;
		}

		var item = document.getElementById(obj);
		if (item.style.display=='none'){
			item.style.display='';
			item.style.top = y;
		} else {
			item.style.display='none';
		}
	}


	function invertView(id) {
		var element = document.getElementById(id);
		var elementImg = document.getElementById('img'+id);
		var address = '../../includes/icons/';

		if (element.style.display=='none'){
			element.style.display='';
			elementImg.src = address+'close.png';
		} else {
			element.style.display='none';
			elementImg.src = address+'open.png';
		}
	}




	function addEvent( id, type, fn ) {
		var obj = document.getElementById(id);

		if ( obj.attachEvent ) {
			obj['e'+type+fn] = fn;
			obj[type+fn] = function(){obj['e'+type+fn]( window.event );}
			obj.attachEvent( 'on'+type, obj[type+fn] );
		} else
			obj.addEventListener( type, fn, false );
	}

	function removeEvent( id, type, fn ) {
		var obj = document.getElementById(id);
		if ( obj.detachEvent ) {
			obj.detachEvent( 'on'+type, obj[type+fn] );
			obj[type+fn] = null;
		} else
			obj.removeEventListener( type, fn, false );
	}


	function Mouse() {
		var isIE = document.all;
		var ns6  = document.getElementById && !document.all;
		var ieTB = (document.compatMode && document.compatMode!="BackCompat")?document.documentElement:document.body;
		var px = null;
		var py = null;


		this.setEvent = function(e) {
			px = (ns6)?e.pageX:event.clientX+ieTB.scrollLeft;
			py = (ns6)?e.pageY:event.clientY+ieTB.scrollTop;
		}

		this.x = function() { return px; }

		this.y = function() { return py; }
	}

	function mouseMoveManager(e) {
		mouse.setEvent(e);
		//document.title = "Cursor_x: "+mouse.x()+" | Cursor_y: "+mouse.y();
	}

	function fecha()
	{
// 		if (history.back){
// 			return history.back();
// 		} else
// 			window.close();

		if (window.opener){
			return window.close();
		} else
			return history.back();
	}


	function showToolTip(e,text,id1, id2){
		if(document.all)e = event;

		var obj = document.getElementById(id1);
		var obj2 = document.getElementById(id2);
		obj2.innerHTML = text;
		obj.style.display = 'block';
		var st = Math.max(document.body.scrollTop,document.documentElement.scrollTop);
		if(navigator.userAgent.toLowerCase().indexOf('safari')>=0)st=0;
		var leftPos = e.clientX - 100;
		if(leftPos<0)leftPos = 0;
		obj.style.left = leftPos + 'px';
		obj.style.top = e.clientY - obj.offsetHeight -1 + st + 'px';
	}

	function hideToolTip(id)
	{
		document.getElementById(id).style.display = 'none';

	}

	function replaceAll( str, from, to ) {
		var idx = str.indexOf( from );
		while ( idx > -1 ) {
			str = str.replace( from, to );
			idx = str.indexOf( from );
		}
		return str;
	}

	function trim(str) {
		return str.replace(/^\s+|\s+$/g,"");
	}

	function foco(id){
		obj = document.getElementById(id);
		obj.focus();
		return true;
	}

	function ajaxFunction(div,script,divLoad){
		var ajaxRequest;  // The variable that makes Ajax possible!

		try{
			// Opera 8.0+, Firefox, Safari
			ajaxRequest = new XMLHttpRequest();
		} catch (e){
			// Internet Explorer Browsers
			try{
				ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				try{
					ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (e){
					// Something went wrong
					alert("Your browser broke!");
					return false;
				}
			}
		}
		// Create a function that will receive data sent from the server
		ajaxRequest.onreadystatechange = function(){
			if(ajaxRequest.readyState == 4){
				document.getElementById(divLoad).style.display = 'none';
				var ajaxDisplay = document.getElementById(div);
				ajaxDisplay.innerHTML = ajaxRequest.responseText;
			} else {
				document.getElementById(divLoad).style.display = '';
			}
		}

		var args = ajaxFunction.arguments.length;
		var i;
		var j;
		var array = new Array();

		for (i=3; i<args; i++){//Jogando os argumentos (apartir do terceiro pois os tres primeiros sao fixos) para um array
			j = i-3;
			array[j] = ajaxFunction.arguments[i];
		}

		var queryString = MontaQueryString(array);

		ajaxRequest.open("GET", script + queryString, true);
		ajaxRequest.send(null);
	}

	function MontaQueryString (array) {
		var i;
		var size = array.length;
		var queryString = '?';

		for (i=0; i<size; i++){
			var param = array[i].split('=');
			param[1] = document.getElementById(param[1]).value;

			queryString += param[0] + "=" + param[1] + "&";
		}
		return queryString;
	}



	function ajaxFunctionPost(div,script,divLoad){
		var ajaxRequest;  // The variable that makes Ajax possible!

		try{
			// Opera 8.0+, Firefox, Safari
			ajaxRequest = new XMLHttpRequest();
		} catch (e){
			// Internet Explorer Browsers
			try{
				ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				try{
					ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (e){
					// Something went wrong
					alert("Your browser broke!");
					return false;
				}
			}
		}
		// Create a function that will receive data sent from the server
		ajaxRequest.onreadystatechange = function(){
			if(ajaxRequest.readyState == 4 && ajaxRequest.status == 200){
				
                
                
                var imgLoad = document.getElementById(divLoad);
                if (imgLoad != null){
                    
                    document.getElementById(divLoad).style.display = 'none';
                    
                } else {
                    console.log('animacao de carregar eh nula');
                }
                
                //document.getElementById(divLoad).style.display = 'none';
				
                var divDestino = document.getElementById(div);
                if (divDestino != null){
                    
                    var ajaxDisplay = document.getElementById(div);
                    ajaxDisplay.innerHTML = ajaxRequest.responseText;
                    
                } else {
                    
                    console.log('Div de destino do AjaxPost eh nula: '+div);
                    
                    var ajaxDisplay = document.getElementById(div);
                    ajaxDisplay.innerHTML = ajaxRequest.responseText;                    
                    
                }
                
                
                
                /*
                document.getElementById(divLoad).style.display = 'none';
				var ajaxDisplay = document.getElementById(div);
				ajaxDisplay.innerHTML = ajaxRequest.responseText;
			*/
                
                
            } else {
				document.getElementById(divLoad).style.display = '';
			}
		}

		var args = ajaxFunctionPost.arguments.length;
		var i;
		var j;
		var array = new Array();

		for (i=3; i<args; i++){//Jogando os argumentos (apartir do terceiro pois os tres primeiros sao fixos) para um array
			j = i-3;
			array[j] = ajaxFunctionPost.arguments[i];
		}

		var queryString = MontaQueryStringPost(array);

		//ajaxRequest.open("GET", script + queryString, true);
		ajaxRequest.open("POST", script , true);
		ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxRequest.send(queryString);
	}

	function MontaQueryStringPost(array) {
		var i;
		var size = array.length;
		var queryString = '';
                var arrayString = "";//NOVA
                //var indice = new Array();
                

		for (i=0; i<size; i++){
			var param = array[i].split('=');

			
			
                        if (param[1].includes("[]")){ //parametro de array sem indice
                            
                            var arg = document.getElementsByName(param[1]);
                            
                            for (var k = 0, len = arg.length; k < len; k++) {
                                
                                var param1 = param[0]+k;
                                var param2 = arg[k].value;
                                
                                arrayString += param1 + "=" + param2 + "&";
                                
                            }                            
                            
                        } else                    
                            
                        if (param[1].includes("[")){ //parametro de array com indice - sera uma variavel
                        
                            var aux1 = param[1].substring(0,param[1].indexOf("["));
                            var elementoSemIndice = aux1.concat("[]");

                            var arg2 = document.getElementsByName(elementoSemIndice);
                            
                            for (var j = 0, len2 = arg2.length; j < len2; j++) {
                                
                                var indice = param[1].substring(param[1].indexOf("[")+1,param[1].indexOf("]"));
                                
                                var param1 = param[0]+indice;
                                var param2 = arg2[j].value;
                                
                                arrayString += param1 + "=" + param2 + "&";
                                
                            }                            

                        } else
                            param[1] = document.getElementById(param[1]).value;
                        
                        queryString += param[0] + "=" + param[1] + "&" + arrayString;
		}
		
		return queryString;
	}



	function check_all(valor){
		
		with(document)
		{
			var d;
			d=document.getElementsByTagName("input");
			
			for(i=0;i<d.length;i++)
			{
				if(d[i].type=="checkbox")
				{
					d[i].checked=valor;
				}
			}
		}
	}


	function _(el){
		return document.getElementById(el);
	}

	function showElements(oFormID) {
            
		var oForm = document.getElementById(oFormID);
		var ID = "";
		
		str = "Form Elements of form " + oForm.name + ": \n"
		
		for (i = 0; i < oForm.length; i++)
			
			str += oForm.elements[i].name + " (" + oForm.elements[i].id + "): " + oForm.elements[i].value + "\n";
		
		console.log(str);
	} 

	function removeParam(key, queryString) {
		var params_arr = [],
		rtn;
			
		if (queryString !== "") {
			params_arr = queryString.split("&");
			for (var i = params_arr.length - 1; i >= 0; i -= 1) {
				param = params_arr[i].split("=")[0];
				if (param === key) {
					params_arr.splice(i, 1);
				}
			}
			rtn = params_arr.join("&");
		}
		return rtn;
	}

