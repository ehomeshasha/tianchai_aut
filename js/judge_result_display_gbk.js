var i=0;
var pats=new Array();
var exps=new Array();

pats[i]=/not a statement/;
exps[i++]="��������{}ƥ�������eclipse��������ݼ�Ctrl+Shift+F";
pats[i]=/class, interface, or enum expected/;
exps[i++]="�벻Ҫ��java�������������������������ⲿ��ע������ŵĽ���λ��}";
pats[i]=/asm.*java/;
exps[i++]="�벻Ҫ��java�����ύΪC����";
pats[i]=/package .* does not exist/;
exps[i++]="���ƴд���磺ϵͳ����SystemΪ��дS��ͷ";
pats[i]=/possible loss of precision/;
exps[i++]="��ֵ����ʧȥ���ȣ�����������ͣ���ȷ���������ʹ��ǿ������ת��";
pats[i]=/incompatible types/;
exps[i++]="Java�в�ͬ���͵����ݲ��ܻ��ำֵ������������������ֵ";
pats[i]=/illegal start of expression/;
exps[i++]="�ַ���Ӧ��Ӣ��˫����(\")����";
pats[i]=/cannot find symbol/;
exps[i++]="ƴд�������ȱ�ٵ��ú�������Ķ�����println()���System.out����";
pats[i]=/';' expected/;
exps[i++]="ȱ�ٷֺš�";
pats[i]=/should be declared in a file named/;
exps[i++]="Java����ʹ��public class Main��";

pats[i]=/expected ��.*�� at end of input/;
exps[i++]="����û�н�����ȱ��ƥ������Ż�ֺţ���鸴��ʱ�Ƿ�ѡ����ȫ�����롣";
pats[i]=/invalid conversion from ��.*�� to ��.*��/;
exps[i++]="����������ת����Ч����������ʾ��ǿ������ת����(int *)malloc(....)";
pats[i]=/warning.*declaration of 'main' with no type/;
exps[i++]="C++��׼�У�main���������з���ֵ";
pats[i]=/'.*' was not declared in this scope/;
exps[i++]="����û����������������Ƿ�ƴд����";
pats[i]=/main�� must return ��int��/;
exps[i++]="�ڱ�׼C�����У�main��������ֵ���ͱ�����int���̲ĺ�VC��ʹ��void�ǷǱ�׼���÷�";
pats[i]=/ .* was not declared in this scope/;
exps[i++]="���������û���������ͽ��е��ã�������Ƿ�������ȷ��ͷ�ļ�";
pats[i]=/printf.*was not declared in this scope/;
exps[i++]="printf����û���������ͽ��е��ã�������Ƿ�����stdio.h��cstdioͷ�ļ�";
pats[i]=/ warning: ignoring return value of/;
exps[i++]="���棺�����˺����ķ���ֵ�������Ǻ����ô����û�п��ǵ�����ֵ�쳣�����";
pats[i]=/:.*__int64�� undeclared/;
exps[i++]="__int64û���������ڱ�׼C/C++�в�֧��΢��VC�е�__int64,��ʹ��long long������64λ����";
pats[i]=/:.*expected ��;�� before/;
exps[i++]="ǰһ��ȱ�ٷֺ�";
pats[i]=/ .* undeclared \(first use in this function\)/;
exps[i++]="����ʹ��ǰ�����Ƚ���������Ҳ�п�����ƴд����ע���Сд���֡�";
pats[i]=/scanf.*was not declared in this scope/;
exps[i++]="scanf����û���������ͽ��е��ã�������Ƿ�����stdio.h��cstdioͷ�ļ�";
pats[i]=/memset.*was not declared in this scope/;
exps[i++]="memset����û���������ͽ��е��ã�������Ƿ�����stdlib.h��cstdlibͷ�ļ�";
pats[i]=/malloc.*was not declared in this scope/;
exps[i++]="malloc����û���������ͽ��е��ã�������Ƿ�����stdlib.h��cstdlibͷ�ļ�";
pats[i]=/puts.*was not declared in this scope/;
exps[i++]="puts����û���������ͽ��е��ã�������Ƿ�����stdio.h��cstdioͷ�ļ�";
pats[i]=/gets.*was not declared in this scope/;
exps[i++]="gets����û���������ͽ��е��ã�������Ƿ�����stdio.h��cstdioͷ�ļ�";
pats[i]=/str.*was not declared in this scope/;
exps[i++]="string�ຯ��û���������ͽ��е��ã�������Ƿ�����string.h��cstringͷ�ļ�";
pats[i]=/��import�� does not name a type/;
exps[i++]="��Ҫ��Java���Գ����ύΪC/C++,�ύǰע��ѡ���������͡�";
pats[i]=/asm�� undeclared/;
exps[i++]="��������C/C++��Ƕ�������Դ��롣";
pats[i]=/redefinition of/;
exps[i++]="����������ظ����壬�����Ƿ���ճ�����롣";
pats[i]=/expected declaration or statement at end of input/;
exps[i++]="�������ûд�꣬�����Ƿ���ճ��ʱ©�����롣";
pats[i]=/warning: unused variable/;
exps[i++]="���棺����������û��ʹ�ã�������Ƿ�ƴд�����������������Ƶı�����";
pats[i]=/implicit declaration of function/;
exps[i++]="��������������������Ƿ�������ȷ��ͷ�ļ���";
pats[i]=/too .* arguments to function/;
exps[i++]="��������ʱ�ṩ�Ĳ����������ԣ�������Ƿ��ô������";
pats[i]=/expected ��=��, ��,��, ��;��, ��asm�� or ��__attribute__�� before ��namespace��/;
exps[i++]="��Ҫ��C++���Գ����ύΪC,�ύǰע��ѡ���������͡�";
pats[i]=/stray ��\\[0123456789]*�� in program/;
exps[i++]="���Ŀո񡢱��Ȳ��ܳ����ڳ�����ע�ͺ��ַ�������Ĳ��֡�";
pats[i]=/division by zero/;
exps[i++]="�����㽫���¸��������";
pats[i]=/cannot be used as a function/;
exps[i++]="�������ܵ��ɺ����ã����������ͺ������ظ��������Ҳ������ƴд����";
pats[i]=/format .* expects type .* but argument .* has type .*/;
exps[i++]="scanf/printf�ĸ�ʽ�����ͺ���Ĳ�����һ�£�����Ƿ���˻�����ȡַ����&����Ҳ������ƴд����";
pats[i]=/��.*�ǹ����ģ�Ӧ����Ϊ .*java ���ļ�������/;
exps[i++]="Java�����ύֻ����һ��public�࣬��������������Main���������벻Ҫ��public�ؼ���";

function getExplanation() {
	var error, error_html, pat, exp, ret;
	jQuery(".error_text").each(function(){
		error = jQuery(this);
		error_html = error.html();
		var expmsg="�������ͣ�<br>";
		for(var i=0;i<pats.length;i++){
			pat=pats[i];
			exp=exps[i];
			ret=pat.exec(error_html);
			if(ret) {
				expmsg += ret+":"+exp+"<br/>";
			}
		}
		
		expmsg = expmsg == "�������ͣ�<br>" ? "" : expmsg;
		error.next().html(expmsg);
	});
}