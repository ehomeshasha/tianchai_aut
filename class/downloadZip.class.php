<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


/**
 * �����ļ�
 *
 */
    class downloadZip{
        protected $_filename;
        protected $_filepath;
        protected $_filesize;//�ļ���С
        public function __construct($filename){
        	//echo $filename;
            $this->_filename=$filename;
            $this->_filepath=$filename;
        }
        //��ȡ�ļ���
        public function getfilename(){
            return $this->_filename;
        }
        
        //��ȡ�ļ�·���������ļ�����
        public function getfilepath(){
            return $this->_filepath;
        }
        
        //��ȡ�ļ���С
        public function getfilesize(){
            return $this->_filesize=number_format(filesize($this->_filepath)/(1024*1024),2);//ȥС�������λ
        }
        
        
        
        
        //�����ļ��Ĺ���
        public function getfiles(){
            //����ļ��Ƿ����
            //echo $this->_filepath;
            if (file_exists($this->_filepath)){
                //���ļ�
                $file = fopen($this->_filepath,"r");
                //���ص��ļ�����
                Header("Content-type: application/octet-stream");
                //�����ֽڴ�С����
                Header("Accept-Ranges: bytes");
                //�����ļ��Ĵ�С
                Header("Accept-Length: ".filesize($this->_filepath));
                //����Կͻ��˵ĵ����Ի��򣬶�Ӧ���ļ���
                Header("Content-Disposition: attachment; filename=".$this->_filename);
                //�޸�֮ǰ��һ���Խ����ݴ�����ͻ���
                echo fread($file, filesize($this->_filepath));
                //�޸�֮��һ��ֻ����1024���ֽڵ����ݸ��ͻ���
                //��ͻ��˻�������
                $buffer=1024;//
                //�ж��ļ��Ƿ����
                while (!feof($file)) {
                    //���ļ������ڴ�
                    $file_data=fread($file,$buffer);
                    //ÿ����ͻ��˻���1024���ֽڵ�����
                    echo $file_data;
                }
                
                fclose($file);
            }else {
                echo "<script>alert('�Բ���,��Ҫ���ص��ļ�������');</script>";
            }
        }
    }
?>