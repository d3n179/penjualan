
Prado.Validation=Class.create();Object.extend(Prado.Validation,{managers:{},validate:function(formID,groupID,invoker)
{formID=formID||this.getForm();if(this.managers[formID])
{return this.managers[formID].validate(groupID,invoker);}
else
{throw new Error("Form '"+formID+"' is not registered with Prado.Validation");}},validateControl:function(id)
{var formId=this.getForm();if(this.managers[formId])
{return this.managers[formId].validateControl(id);}else{throw new Error("A validation manager needs to be created first.");}},getForm:function()
{var keys=$H(this.managers).keys();return keys[0];},isValid:function(formID,groupID)
{formID=formID||this.getForm();if(this.managers[formID])
return this.managers[formID].isValid(groupID);return true;},reset:function(groupID)
{var formID=this.getForm();if(this.managers[formID])
this.managers[formID].reset(groupID);},addValidator:function(formID,validator)
{if(this.managers[formID])
this.managers[formID].addValidator(validator);else
throw new Error("A validation manager for form '"+formID+"' needs to be created first.");return this.managers[formID];},addSummary:function(formID,validator)
{if(this.managers[formID])
this.managers[formID].addSummary(validator);else
throw new Error("A validation manager for form '"+formID+"' needs to be created first.");return this.managers[formID];},setErrorMessage:function(validatorID,message)
{$H(Prado.Validation.managers).each(function(manager)
{manager[1].validators.each(function(validator)
{if(validator.options.ID==validatorID)
{validator.options.ErrorMessage=message;$(validatorID).innerHTML=message;}});});},updateActiveCustomValidator:function(validatorID,isValid)
{$H(Prado.Validation.managers).each(function(manager)
{manager[1].validators.each(function(validator)
{if(validator.options.ID==validatorID)
{validator.updateIsValid(isValid);}});});}});Prado.ValidationManager=Class.create();Prado.ValidationManager.prototype={controls:{},initialize:function(options)
{if(!Prado.Validation.managers[options.FormID])
{this.validators=[];this.summaries=[];this.groups=[];this.options={};this.options=options;Prado.Validation.managers[options.FormID]=this;}
else
{var manager=Prado.Validation.managers[options.FormID];this.validators=manager.validators;this.summaries=manager.summaries;this.groups=manager.groups;this.options=manager.options;}},reset:function(group)
{this.validatorPartition(group)[0].invoke('reset');this.updateSummary(group,true);},validate:function(group,source)
{var partition=this.validatorPartition(group);var valid=partition[0].invoke('validate',source).all();this.focusOnError(partition[0]);partition[1].invoke('hide');this.updateSummary(group,true);return valid;},validateControl:function(id)
{return this.controls[id]?this.controls[id].invoke('validate',null).all():true;},focusOnError:function(validators)
{for(var i=0;i<validators.length;i++)
{if(!validators[i].isValid&&validators[i].options.FocusOnError)
return Prado.Element.focus(validators[i].options.FocusElementID);}},validatorPartition:function(group)
{return group?this.validatorsInGroup(group):this.validatorsWithoutGroup();},validatorsInGroup:function(groupID)
{if(this.groups.include(groupID))
{return this.validators.partition(function(val)
{return val.group==groupID;});}
else
return[[],[]];},validatorsWithoutGroup:function()
{return this.validators.partition(function(val)
{return!val.group;});},isValid:function(group)
{return this.validatorPartition(group)[0].pluck('isValid').all();},addValidator:function(validator)
{this.removeValidator(validator);this.validators.push(validator);if(validator.group&&!this.groups.include(validator.group))
this.groups.push(validator.group);if(typeof this.controls[validator.control.id]==='undefined')
this.controls[validator.control.id]=Array();this.controls[validator.control.id].push(validator);},addSummary:function(summary)
{this.summaries.push(summary);},removeValidator:function(validator)
{this.validators=this.validators.reject(function(v)
{return(v.options.ID==validator.options.ID);});if(this.controls[validator.control.id])
this.controls[validator.control.id].reject(function(v)
{return(v.options.ID==validator.options.ID)});},getValidatorsWithError:function(group)
{return this.validatorPartition(group)[0].findAll(function(validator)
{return!validator.isValid;});},updateSummary:function(group,refresh)
{var validators=this.getValidatorsWithError(group);this.summaries.each(function(summary)
{var inGroup=group&&summary.group==group;var noGroup=!group||!summary.group;if(inGroup||noGroup)
summary.updateSummary(validators,refresh);else
summary.hideSummary(true);});}};Prado.WebUI.TValidationSummary=Class.create();Prado.WebUI.TValidationSummary.prototype={initialize:function(options)
{this.options=options;this.group=options.ValidationGroup;this.messages=$(options.ID);Prado.Registry.set(options.ID,this);if(this.messages)
{this.visible=this.messages.style.visibility!="hidden"
this.visible=this.visible&&this.messages.style.display!="none";Prado.Validation.addSummary(options.FormID,this);}},updateSummary:function(validators,update)
{if(validators.length<=0)
{if(update||this.options.Refresh!=false)
{return this.hideSummary(validators);}
return;}
var refresh=update||this.visible==false||this.options.Refresh!=false;refresh=refresh&&validators.any(function(v){return!v.requestDispatched;});if(this.options.ShowSummary!=false&&refresh)
{this.updateHTMLMessages(this.getMessages(validators));this.showSummary(validators);}
if(this.options.ScrollToSummary!=false&&refresh)
window.scrollTo(this.messages.offsetLeft-20,this.messages.offsetTop-20);if(this.options.ShowMessageBox==true&&refresh)
{this.alertMessages(this.getMessages(validators));this.visible=true;}},updateHTMLMessages:function(messages)
{while(this.messages.childNodes.length>0)
this.messages.removeChild(this.messages.lastChild);this.messages.insert(this.formatSummary(messages));},alertMessages:function(messages)
{var text=this.formatMessageBox(messages);setTimeout(function(){alert(text);},20);},getMessages:function(validators)
{var messages=[];validators.each(function(validator)
{var message=validator.getErrorMessage();if(typeof(message)=='string'&&message.length>0)
messages.push(message);})
return messages;},hideSummary:function(validators)
{if(typeof(this.options.OnHideSummary)=="function")
{this.messages.style.visibility="visible";this.options.OnHideSummary(this,validators)}
else
{this.messages.style.visibility="hidden";if(this.options.Display=="None"||this.options.Display=="Dynamic")
this.messages.hide();}
this.visible=false;},showSummary:function(validators)
{this.messages.style.visibility="visible";if(typeof(this.options.OnShowSummary)=="function")
this.options.OnShowSummary(this,validators);else
this.messages.show();this.visible=true;},formats:function(type)
{switch(type)
{case"SimpleList":return{header:"<br />",first:"",pre:"",post:"<br />",last:""};case"SingleParagraph":return{header:" ",first:"",pre:"",post:" ",last:"<br />"};case"HeaderOnly":return{header:"",first:"<!--",pre:"",post:"",last:"-->"};case"BulletList":default:return{header:"",first:"<ul>",pre:"<li>",post:"</li>",last:"</ul>"};}},formatSummary:function(messages)
{var format=this.formats(this.options.DisplayMode);var output=this.options.HeaderText?this.options.HeaderText+format.header:"";output+=format.first;messages.each(function(message)
{output+=message.length>0?format.pre+message+format.post:"";});output+=format.last;return output;},formatMessageBox:function(messages)
{if(this.options.DisplayMode=='HeaderOnly'&&this.options.HeaderText)
return this.options.HeaderText;var output=this.options.HeaderText?this.options.HeaderText+"\n":"";for(var i=0;i<messages.length;i++)
{switch(this.options.DisplayMode)
{case"List":output+=messages[i]+"\n";break;case"BulletList":default:output+="  - "+messages[i]+"\n";break;case"SingleParagraph":output+=messages[i]+" ";break;}}
return output;}};Prado.WebUI.TBaseValidator=Class.create(Prado.WebUI.Control,{initialize:function(options)
{this.observers=new Array();this.intervals=new Array();this.enabled=options.Enabled;this.visible=false;this.isValid=true;this._isObserving={};this.group=null;this.requestDispatched=false;this.options=options;this.control=$(options.ControlToValidate);this.message=$(options.ID);Prado.Registry.set(options.ID,this);if(this.onInit)this.onInit();if(this.control&&this.message)
{this.group=options.ValidationGroup;this.manager=Prado.Validation.addValidator(options.FormID,this);}},getErrorMessage:function()
{return this.options.ErrorMessage;},updateControl:function()
{this.refreshControlAndMessage();this.visible=true;},refreshControlAndMessage:function()
{this.visible=true;if(this.message)
{if(this.options.Display=="Dynamic")
{var msg=this.message;this.isValid?setTimeout(function(){msg.hide();},250):msg.show();}
this.message.style.visibility=this.isValid?"hidden":"visible";}
if(this.control)
this.updateControlCssClass(this.control,this.isValid);},updateControlCssClass:function(control,valid)
{var CssClass=this.options.ControlCssClass;if(typeof(CssClass)=="string"&&CssClass.length>0)
{if(valid)
{if(control.lastValidator==this.options.ID)
{control.lastValidator=null;control.removeClassName(CssClass);}}
else
{control.lastValidator=this.options.ID;control.addClassName(CssClass);}}},hide:function()
{this.reset();this.visible=false;},reset:function()
{this.isValid=true;this.updateControl();},validate:function(invoker)
{if(!this.control)
this.control=$(this.options.ControlToValidate);if(!this.control||this.control.disabled)
{this.isValid=true;return this.isValid;}
if(typeof(this.options.OnValidate)=="function")
{if(this.requestDispatched==false)
this.options.OnValidate(this,invoker);}
if(this.enabled&&!this.control.getAttribute('disabled'))
this.isValid=this.evaluateIsValid();else
this.isValid=true;this.updateValidationDisplay(invoker);this.observeChanges(this.control);return this.isValid;},updateValidationDisplay:function(invoker)
{if(this.isValid)
{if(typeof(this.options.OnValidationSuccess)=="function")
{if(this.requestDispatched==false)
{this.refreshControlAndMessage();this.options.OnValidationSuccess(this,invoker);}}
else
this.updateControl();}
else
{if(typeof(this.options.OnValidationError)=="function")
{if(this.requestDispatched==false)
{this.refreshControlAndMessage();this.options.OnValidationError(this,invoker)}}
else
this.updateControl();}},observeChanges:function(control)
{if(!control)return;var canObserveChanges=this.options.ObserveChanges!=false;var currentlyObserving=this._isObserving[control.id+this.options.ID];if(canObserveChanges&&!currentlyObserving)
{var validator=this;this.observe(control,'change',function()
{if(validator.visible)
{validator.validate();validator.manager.updateSummary(validator.group);}});this._isObserving[control.id+this.options.ID]=true;}},trim:function(value)
{return typeof(value)=="string"?value.trim():"";},convert:function(dataType,value)
{if(typeof(value)=="undefined")
value=this.getValidationValue();var string=new String(value);switch(dataType)
{case"Integer":return string.toInteger();case"Double":case"Float":return string.toDouble(this.options.DecimalChar);case"Date":if(typeof(value)!="string")
return value;else
{var value=string.toDate(this.options.DateFormat);if(value&&typeof(value.getTime)=="function")
return value.getTime();else
return null;}
case"String":return string.toString();}
return value;},getRawValidationValue:function(control)
{if(!control)
control=this.control
switch(this.options.ControlType)
{case'TDatePicker':if(control.type=="text")
{var value=this.trim($F(control));if(this.options.DateFormat)
{var date=value.toDate(this.options.DateFormat);return date==null?value:date;}
else
return value;}
else
{this.observeDatePickerChanges();return Prado.WebUI.TDatePicker.getDropDownDate(control);}
case'THtmlArea':if(typeof tinyMCE!="undefined")
tinyMCE.triggerSave();return $F(control);case'TRadioButton':if(this.options.GroupName)
return this.getRadioButtonGroupValue();default:if(this.isListControlType())
return this.getFirstSelectedListValue();else
return $F(control);}},getValidationValue:function(control)
{var value=this.getRawValidationValue(control);if(!control)
control=this.control
switch(this.options.ControlType)
{case'TDatePicker':return value;case'THtmlArea':return this.trim(value);case'TRadioButton':return value;default:if(this.isListControlType())
return value;else
return this.trim(value);}},getRadioButtonGroupValue:function()
{var name=this.control.name;var value="";$A(document.getElementsByName(name)).each(function(el)
{if(el.checked)
value=el.value;});return value;},observeDatePickerChanges:function()
{if(Prado.Browser().ie)
{var DatePicker=Prado.WebUI.TDatePicker;this.observeChanges(DatePicker.getDayListControl(this.control));this.observeChanges(DatePicker.getMonthListControl(this.control));this.observeChanges(DatePicker.getYearListControl(this.control));}},getSelectedValuesAndChecks:function(elements,initialValue)
{var checked=0;var values=[];var isSelected=this.isCheckBoxType(elements[0])?'checked':'selected';elements.each(function(element)
{if(element[isSelected]&&element.value!=initialValue)
{checked++;values.push(element.value);}});return{'checks':checked,'values':values};},getListElements:function()
{switch(this.options.ControlType)
{case'TCheckBoxList':case'TRadioButtonList':var elements=[];for(var i=0;i<this.options.TotalItems;i++)
{var element=$(this.options.ControlToValidate+"_c"+i);if(this.isCheckBoxType(element))
elements.push(element);}
return elements;case'TListBox':var elements=[];var element=$(this.options.ControlToValidate);var type;if(element&&(type=element.type.toLowerCase()))
{if(type=="select-one"||type=="select-multiple")
elements=$A(element.options);}
return elements;default:return[];}},isCheckBoxType:function(element)
{if(element&&element.type)
{var type=element.type.toLowerCase();return type=="checkbox"||type=="radio";}
return false;},isListControlType:function()
{var list=['TCheckBoxList','TRadioButtonList','TListBox'];return list.include(this.options.ControlType);},getFirstSelectedListValue:function()
{var initial="";if(typeof(this.options.InitialValue)!="undefined")
initial=this.options.InitialValue;var elements=this.getListElements();var selection=this.getSelectedValuesAndChecks(elements,initial);return selection.values.length>0?selection.values[0]:initial;}});Prado.WebUI.TRequiredFieldValidator=Class.extend(Prado.WebUI.TBaseValidator,{evaluateIsValid:function()
{var a=this.getValidationValue();var b=this.trim(this.options.InitialValue);return(a!=b);}});Prado.WebUI.TCompareValidator=Class.extend(Prado.WebUI.TBaseValidator,{evaluateIsValid:function()
{var value=this.getValidationValue();if(value.length<=0)
return true;var comparee=$(this.options.ControlToCompare);if(comparee)
var compareTo=this.getValidationValue(comparee);else
var compareTo=this.options.ValueToCompare||"";var isValid=this.compare(value,compareTo);if(comparee)
{this.updateControlCssClass(comparee,isValid);this.observeChanges(comparee);}
return isValid;},compare:function(operand1,operand2)
{var op1,op2;if((op1=this.convert(this.options.DataType,operand1))==null)
return false;if((op2=this.convert(this.options.DataType,operand2))==null)
return true;switch(this.options.Operator)
{case"NotEqual":return(op1!=op2);case"GreaterThan":return(op1>op2);case"GreaterThanEqual":return(op1>=op2);case"LessThan":return(op1<op2);case"LessThanEqual":return(op1<=op2);default:return(op1==op2);}}});Prado.WebUI.TCustomValidator=Class.extend(Prado.WebUI.TBaseValidator,{evaluateIsValid:function()
{var value=this.getValidationValue();var clientFunction=this.options.ClientValidationFunction;if(typeof(clientFunction)=="string"&&clientFunction.length>0)
{var validate=clientFunction.toFunction();return validate(this,value);}
return true;}});Prado.WebUI.TActiveCustomValidator=Class.extend(Prado.WebUI.TBaseValidator,{validate:function(invoker)
{this.invoker=invoker;if(!this.control)
this.control=$(this.options.ControlToValidate);if(!this.control||this.control.disabled)
{this.isValid=true;return this.isValid;}
if(typeof(this.options.OnValidate)=="function")
{if(this.requestDispatched==false)
this.options.OnValidate(this,invoker);}
return true;},evaluateIsValid:function()
{return this.isValid;},updateIsValid:function(data)
{this.isValid=data;this.requestDispatched=false;if(typeof(this.options.onSuccess)=="function")
this.options.onSuccess(null,data);this.updateValidationDisplay();this.manager.updateSummary(this.group);}});Prado.WebUI.TRangeValidator=Class.extend(Prado.WebUI.TBaseValidator,{evaluateIsValid:function()
{var value=this.getValidationValue();if(value.length<=0)
return true;if(typeof(this.options.DataType)=="undefined")
this.options.DataType="String";if(this.options.DataType!="StringLength")
{var min=this.convert(this.options.DataType,this.options.MinValue||null);var max=this.convert(this.options.DataType,this.options.MaxValue||null);value=this.convert(this.options.DataType,value);}
else
{var min=this.options.MinValue||0;var max=this.options.MaxValue||Number.POSITIVE_INFINITY;value=value.length;}
if(value==null)
return false;var valid=true;if(min!=null)
valid=valid&&(this.options.StrictComparison?value>min:value>=min);if(max!=null)
valid=valid&&(this.options.StrictComparison?value<max:value<=max);return valid;}});Prado.WebUI.TRegularExpressionValidator=Class.extend(Prado.WebUI.TBaseValidator,{evaluateIsValid:function()
{var value=this.getRawValidationValue();if(value.length<=0)
return true;var rx=new RegExp('^'+this.options.ValidationExpression+'$',this.options.PatternModifiers);var matches=rx.exec(value);return(matches!=null&&value==matches[0]);}});Prado.WebUI.TEmailAddressValidator=Prado.WebUI.TRegularExpressionValidator;Prado.WebUI.TListControlValidator=Class.extend(Prado.WebUI.TBaseValidator,{evaluateIsValid:function()
{var elements=this.getListElements();if(elements&&elements.length<=0)
return true;this.observeListElements(elements);var selection=this.getSelectedValuesAndChecks(elements);return this.isValidList(selection.checks,selection.values);},observeListElements:function(elements)
{if(Prado.Browser().ie&&this.isCheckBoxType(elements[0]))
{var validator=this;elements.each(function(element)
{validator.observeChanges(element);});}},isValidList:function(checked,values)
{var exists=true;var required=this.getRequiredValues();if(required.length>0)
{if(values.length<required.length)
return false;required.each(function(requiredValue)
{exists=exists&&values.include(requiredValue);});}
var min=typeof(this.options.Min)=="undefined"?Number.NEGATIVE_INFINITY:this.options.Min;var max=typeof(this.options.Max)=="undefined"?Number.POSITIVE_INFINITY:this.options.Max;return exists&&checked>=min&&checked<=max;},getRequiredValues:function()
{var required=[];if(this.options.Required&&this.options.Required.length>0)
required=this.options.Required.split(/,\s*/);return required;}});Prado.WebUI.TDataTypeValidator=Class.extend(Prado.WebUI.TBaseValidator,{evaluateIsValid:function()
{var value=this.getValidationValue();if(value.length<=0)
return true;return this.convert(this.options.DataType,value)!=null;}});Prado.WebUI.TCaptchaValidator=Class.extend(Prado.WebUI.TBaseValidator,{evaluateIsValid:function()
{var a=this.getValidationValue();var h=0;if(this.options.CaseSensitive==false)
a=a.toUpperCase();for(var i=a.length-1;i>=0;--i)
h+=a.charCodeAt(i);return h==this.options.TokenHash;},crc32:function(str)
{function Utf8Encode(string)
{string=string.replace(/\r\n/g,"\n");var utftext="";for(var n=0;n<string.length;n++)
{var c=string.charCodeAt(n);if(c<128){utftext+=String.fromCharCode(c);}
else if((c>127)&&(c<2048)){utftext+=String.fromCharCode((c>>6)|192);utftext+=String.fromCharCode((c&63)|128);}
else{utftext+=String.fromCharCode((c>>12)|224);utftext+=String.fromCharCode(((c>>6)&63)|128);utftext+=String.fromCharCode((c&63)|128);}}
return utftext;};str=Utf8Encode(str);var table="00000000 77073096 EE0E612C 990951BA 076DC419 706AF48F E963A535 9E6495A3 0EDB8832 79DCB8A4 E0D5E91E 97D2D988 09B64C2B 7EB17CBD E7B82D07 90BF1D91 1DB71064 6AB020F2 F3B97148 84BE41DE 1ADAD47D 6DDDE4EB F4D4B551 83D385C7 136C9856 646BA8C0 FD62F97A 8A65C9EC 14015C4F 63066CD9 FA0F3D63 8D080DF5 3B6E20C8 4C69105E D56041E4 A2677172 3C03E4D1 4B04D447 D20D85FD A50AB56B 35B5A8FA 42B2986C DBBBC9D6 ACBCF940 32D86CE3 45DF5C75 DCD60DCF ABD13D59 26D930AC 51DE003A C8D75180 BFD06116 21B4F4B5 56B3C423 CFBA9599 B8BDA50F 2802B89E 5F058808 C60CD9B2 B10BE924 2F6F7C87 58684C11 C1611DAB B6662D3D 76DC4190 01DB7106 98D220BC EFD5102A 71B18589 06B6B51F 9FBFE4A5 E8B8D433 7807C9A2 0F00F934 9609A88E E10E9818 7F6A0DBB 086D3D2D 91646C97 E6635C01 6B6B51F4 1C6C6162 856530D8 F262004E 6C0695ED 1B01A57B 8208F4C1 F50FC457 65B0D9C6 12B7E950 8BBEB8EA FCB9887C 62DD1DDF 15DA2D49 8CD37CF3 FBD44C65 4DB26158 3AB551CE A3BC0074 D4BB30E2 4ADFA541 3DD895D7 A4D1C46D D3D6F4FB 4369E96A 346ED9FC AD678846 DA60B8D0 44042D73 33031DE5 AA0A4C5F DD0D7CC9 5005713C 270241AA BE0B1010 C90C2086 5768B525 206F85B3 B966D409 CE61E49F 5EDEF90E 29D9C998 B0D09822 C7D7A8B4 59B33D17 2EB40D81 B7BD5C3B C0BA6CAD EDB88320 9ABFB3B6 03B6E20C 74B1D29A EAD54739 9DD277AF 04DB2615 73DC1683 E3630B12 94643B84 0D6D6A3E 7A6A5AA8 E40ECF0B 9309FF9D 0A00AE27 7D079EB1 F00F9344 8708A3D2 1E01F268 6906C2FE F762575D 806567CB 196C3671 6E6B06E7 FED41B76 89D32BE0 10DA7A5A 67DD4ACC F9B9DF6F 8EBEEFF9 17B7BE43 60B08ED5 D6D6A3E8 A1D1937E 38D8C2C4 4FDFF252 D1BB67F1 A6BC5767 3FB506DD 48B2364B D80D2BDA AF0A1B4C 36034AF6 41047A60 DF60EFC3 A867DF55 316E8EEF 4669BE79 CB61B38C BC66831A 256FD2A0 5268E236 CC0C7795 BB0B4703 220216B9 5505262F C5BA3BBE B2BD0B28 2BB45A92 5CB36A04 C2D7FFA7 B5D0CF31 2CD99E8B 5BDEAE1D 9B64C2B0 EC63F226 756AA39C 026D930A 9C0906A9 EB0E363F 72076785 05005713 95BF4A82 E2B87A14 7BB12BAE 0CB61B38 92D28E9B E5D5BE0D 7CDCEFB7 0BDBDF21 86D3D2D4 F1D4E242 68DDB3F8 1FDA836E 81BE16CD F6B9265B 6FB077E1 18B74777 88085AE6 FF0F6A70 66063BCA 11010B5C 8F659EFF F862AE69 616BFFD3 166CCF45 A00AE278 D70DD2EE 4E048354 3903B3C2 A7672661 D06016F7 4969474D 3E6E77DB AED16A4A D9D65ADC 40DF0B66 37D83BF0 A9BCAE53 DEBB9EC5 47B2CF7F 30B5FFE9 BDBDF21C CABAC28A 53B39330 24B4A3A6 BAD03605 CDD70693 54DE5729 23D967BF B3667A2E C4614AB8 5D681B02 2A6F2B94 B40BBE37 C30C8EA1 5A05DF1B 2D02EF8D";var crc=0;var x=0;var y=0;crc=crc^(-1);for(var i=0,iTop=str.length;i<iTop;i++)
{y=(crc^str.charCodeAt(i))&0xFF;x="0x"+table.substr(y*9,8);crc=(crc>>>8)^x;}
return crc^(-1);}});Prado.WebUI.TReCaptchaValidator=Class.create(Prado.WebUI.TBaseValidator,{onInit:function()
{var obj=this;var elements=document.getElementsByName(this.options.ResponseFieldName);if(elements)
if(elements.length>=1)
{this.observe(elements[0],'change',function(){obj.responseChanged()});this.observe(elements[0],'keydown',function(){obj.responseChanged()});}},responseChanged:function()
{var field=$(this.options.ID+'_1');if(field.value=='1')return;field.value='1';Prado.Validation.validateControl(this.options.ID);},evaluateIsValid:function()
{return($(this.options.ID+'_1').value=='1');}});