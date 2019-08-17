﻿CKEDITOR.dialog.add("simplebuttonDialog",function(d){return{title:"Simple Button",minWidth:400,minHeight:200,contents:[{id:"tab-basic",elements:[{type:"text",id:"button-text",label:"Text",validate:CKEDITOR.dialog.validate.notEmpty("Text field cannot be empty."),setup:function(a,b){this.preview_button=b;this.setValue(a.getText())},commit:function(a){a.setText(this.getValue())},onChange:function(){this.preview_button.setText(this.getValue())}},{type:"text",id:"button-url",label:"URL",setup:function(a){this.setValue(a.getAttribute("href"))},
commit:function(a){a.setAttribute("href",this.getValue());a.removeAttribute("data-cke-saved-href")}},{type:"text",id:"font-size",label:"Font Size (px)",validate:CKEDITOR.dialog.validate.regex(/^(?=.+)(?:[1-9]\d*|0)?(?:\.\d+)?$/,"Font size is not valid."),setup:function(a,b){this.preview_button=b;this.setValue(a.getStyle("font-size").split("px")[0])},commit:function(a){a.setStyle("font-size",this.getValue()+"px")},onChange:function(){this.getValue().match(/^(?=.+)(?:[1-9]\d*|0)?(?:\.\d+)?$/)&&this.preview_button.setStyle("font-size",
this.getValue()+"px")}},{type:"text",id:"border-radius",label:"Border Radius (px)",validate:CKEDITOR.dialog.validate.regex(/^\+?(0|[1-9]\d*)$/,"Border Radius is not valid."),setup:function(a,b){this.preview_button=b;this.setValue(a.getStyle("border-radius").split("px")[0])},commit:function(a){a.setStyle("border-radius",this.getValue()+"px")},onChange:function(){this.getValue().match(/^\+?(0|[1-9]\d*)$/)&&this.preview_button.setStyle("border-radius",this.getValue()+"px")}},{type:"html",html:'\x3cp\x3eBackground\x3c/p\x3e\x3cinput style\x3d"width:20%;float:left" class\x3d"cke_dialog_ui_input_text color-text-input" type\x3d"text"\x3e\x3cdiv class\x3d"custom-color-button" style\x3d"width:25px; height:25px;background-size:25px;background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAACwVBMVEUFBQUaGhomJiYzMzNAQEBERERISEhLS0tPT09SUlJVVVVYWFhcXFxfX19iYmJlZWVoaGhra2ttbW1wcHBycnJ1dXV3d3d5eXl7e3t9fX1/f3////8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACAgIAGBgYGBgZ/f3+AgIB3d3d5eXl7e3t9fX1wcHBycnJ1dXVra2ttbW1iYmJlZWVoaGhcXFxfX19VVVVYWFhPT09SUlJISEhLS0tERERAQEAzMzMmJiYaGhoQEBAHBwdYWNNYqtNYxVhZWdxZsNxZzVlbW9dbrtdbyVteXudeuede2F5gYNtgsttgzmBhYephveph22FjY+BjtuBj0WNkZNlksdlkzGRlZe9lwe9l32VnZ+NnuuNn1WdpaeVpauVpvOVp2GlqavNqxfNqxvNq5Gpubvhuyvhu6G5vb/JvxvJv5G9ycvxyzvxy7HKqWNOq01iuW9eu11uwWdyw3FmxZNmx2WSyYNuy22C2Y+C24GO5Xue55166Z+O642e8aeW8auW85Wm9Yeq96mHBZe/B72XFavPF82rGavPGb/LG8m/G82rKbvjK+G7OcvzO/HLTWFjTWKrTqljT09PXW1vXW67XrlvX19fZZGTZZLHZsWTZ2dnbYGDbYLLbsmDb29vcWVncWbDcsFnc3Nzf39/gY2PgY7bgtmPg4ODh4eHi4uLjZ2fjZ7rjumfj4+Pk5OTlaWnlabzlamnlarzlvGnl5eXm5ubnXl7nXrnnuV7n5+fp6enqYWHqYbzqYb3qvWHq6urr6+vs7Ozt7e3vZWXvZcHvwWXv7+/w8PDx8fHyb2/yb8byxm/y8vLzamrzasXzasbzxWrzxmrz8/P09PT19fX29vb4bm74bsr4ym74+Pj5+fn6+vr7+/v8cnL8cs78znL8/Pz9/f3+/v7///8IFeovAAAARnRSTlMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABwgLDQ4PEhUZGk1eYGZmZ2dnZ2hoaGlpampqa2tsbG1tbm5vcHFzdXZ4cX7MKgAAAZFJREFUOMt9kz1uFEEQhb+e6Z1dz9pgYQlWRg7IEQkQE/KTACcAzgUkcAcgJOQcxIS2Z7an6EdQtSsZazzJp1LVVL9X3ZXuc/OXdZjns3aeMu3P+YJnkLVAc/m0UMqS9OMUYHP324amcrr57ryHRJbg5KH/cfLoKqOgWtm64KCCiyyR1aFhTEpiPQyeGEYEZDqRJdN4AUA/XgJwFPGh+RGSRWsbg1tASZLIyrLj4mM5noIRL3J0eByanwaf7Dx4B6tf1wAHL3/5PLovPUn0ryw6lNUtAErM48/qtserEDlZBWAK//t4GSJVSzZAQ7ippamA5EcUK7UA2Hju6kutToms1uQxu3k0+7h1DTV7oob/bhe7zVbTc/c9bYIvgss27uJzD9C//uR88/GAJNZvLTpYd+Ti9vS5mELDdlgDcI1riaxGKn/joVhSEioWd9GIrDRZuQj/l1c5JZGVUBmiw3/ECzS17v8aFR14F4/4ffDDfjFEVuruzC1Ol0R6sDybX73fo6Wztm1m0qpWlWudblz/f2jHKXM/GqmjAAAAAElFTkSuQmCC); float:left; cursor: pointer;"\x3e\x3c/div\x3e\x3ctable class\x3d"colors-table" style\x3d"margin-top: -44px;right: 88px;position: absolute; z-index: 1; display:none"\x3e\x3ctbody\x3e\x3ctr style\x3d"border-bottom: 1px solid #fff;height: 23px;"\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;width: 23px; background-color: #F44236;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;width: 23px; background-color: #DD5561;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;width: 23px; background-color: #E91D62;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;width: 23px; background-color: #363F46;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;width: 23px; background-color: #9C26B0;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;width: 23px; background-color: #6739B6;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;width: 23px; background-color: #2A80B9;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;width: 23px; background-color: #3E50B4;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;width: 23px; background-color: #2095F2;"\x3e\x3c/td\x3e\x3c/tr\x3e\x3ctr style\x3d"border-bottom: 1px solid #fff;height: 23px;"\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;width: 23px; background-color: #02A8F4;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;background-color: #01BBD4;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;background-color: #019587;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;background-color: #27AE61;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;background-color: #1BBC9B;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;background-color: #4BAF4F;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;background-color: #8BC24A;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;background-color: #CCDB38;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;background-color: #FFE93B;"\x3e\x3c/td\x3e\x3c/tr\x3e\x3ctr style\x3d"border-bottom: 1px solid #fff;height: 23px;"\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;background-color: #F39C11;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;background-color: #FEC107;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;background-color: #FF9700;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;background-color: #FF5521;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;background-color: #795549;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;background-color: #9D9D9D;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;background-color: #607C8A;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;background-color: #8F44AD;"\x3e\x3c/td\x3e\x3ctd class\x3d"color-column" style\x3d"cursor:pointer;border-left: 1px solid #fff;background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAADFBMVEXMyszMzszU0tT8/vwq5swEAAAAZElEQVR4Ae3SwQ2EAAgF0Rm2/543Ro04NcjtceH/BEYHh981NSKK96LGc+Ze1Awy8JyIT+pzIkbRFapmUFao+uCwQtU73OjU8AqD1rz7M3X6W0P71+nPbPfEwRrS3/r7h+8ftv/vfQe1Hir3FQAAAABJRU5ErkJggg\x3d\x3d);background-size:23px;background-repeat:no-repeat;"\x3e\x3c/td\x3e\x3c/tr\x3e\x3c/tbody\x3e\x3c/table\x3e'},
{type:"html",html:'\x3cp\x3ePreview\x3c/p\x3e\x3cdiv style\x3d"background-color: #ccc;border: 1px solid #bbb;padding: 10px;text-align: center;"\x3e\x3ca class\x3d"preview-button"\x3e\x3c/a\x3e\x3c/div\x3e',setup:function(a){for(var b=this.getElement().getAscendant("table"),c=b.find(".color-column").$,e=0;e<c.length;e++){var d=c[e].getAttribute("style"),f="transparent",g="#fff";-1<d.indexOf("background-color")&&(g=f=d.split("background-color: ")[1].split(";")[0]);c[e].setAttribute("onclick",'var row \x3d this.closest(".colors-table").parentNode;row.querySelector(".color-text-input").value \x3d "'+
f+'";var preview_button \x3d row.closest("table").querySelector(".preview-button");var color \x3d document.querySelector(".color-text-input").value;preview_button.style["background-color"] \x3d "'+f+'";preview_button.style["border"] \x3d "1px solid '+g+'";row.querySelector(".colors-table").style.display \x3d "none"')}b.findOne(".custom-color-button").setAttribute("onclick",'var colors_table \x3d this.parentNode.querySelector(".colors-table"); if(colors_table.style.display \x3d\x3d "none") colors_table.style.display \x3d "block"; else colors_table.style.display \x3d "none";');
c=b.findOne(".color-text-input");c.setAttribute("value",a.getAttribute("style").split("background-color:")[1].split(";")[0]);c.setAttribute("onchange",'var preview_button \x3d this.closest("table").querySelector(".preview-button");var color \x3d this.value;preview_button.style["background-color"] \x3d color;preview_button.style["border"] \x3d "1px solid " + color;');b=b.findOne(".preview-button");b.setAttribute("style",a.getAttribute("style"));b.setText(a.getText())},commit:function(a){var b=this.getElement().getAscendant("table").findOne(".color-text-input").$.value,
c=b;"transparent"==b&&(c="#fff");a.setStyle("background-color",b);a.setStyle("border","1px solid "+c)}}]}],onShow:function(){var a=d.getSelection().getStartElement();a&&a.hasClass("simple-button-plugin")?this.insertMode=!1:(a=d.document.createElement("a"),a.setAttribute("class","simple-button-plugin"),a.setAttribute("target","_blank"),a.setAttribute("style","display:inline-block;background-color:#27AE61;border:1px solid #27AE61;color:#fff !important;padding:5px 10px;border-radius:5px;font-size:14px;text-decoration: none !important; cursor: pointer;"),
a.setText("Unsubscribe"),this.insertMode=!0);this.element=a;a=this.getElement().findOne(".preview-button");this.setupContent(this.element,a)},onOk:function(){var a=this.element;this.commitContent(a);this.insertMode&&d.insertElement(a)}}});