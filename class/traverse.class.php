<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once AUT_PATH."/class/downloadZip.class.php";
/**
 * ����Ŀ¼�������zip��ʽ
 */
class traverseDir {
	public $currentdir; //��ǰĿ¼
	public $filename; //�ļ���
	public $fileinfo; //���ڱ��浱ǰĿ¼�µ������ļ�����Ŀ¼���Լ��ļ���С
	public function __construct($downloadDir) {
		$this->currentdir = $downloadDir; //���ص�ǰĿ¼
	}
	//����Ŀ¼
	public function scandir($filepath) {
		if (is_dir($filepath)) {
			$arr = scandir($filepath);
			foreach ($arr as $k => $v) {
				$this->fileinfo[$v][] = $this->getfilesize($v);
			}
		} else {
			echo "<script>alert('��ǰĿ¼������ЧĿ¼��Ŀ¼������');</script>";
		}
	}
	/**
	 * �����ļ��Ĵ�С
	 *
	 * @param string $filename �ļ���
	 * @return �ļ���С(KB)
	 */
	public function getfilesize($fname) {
		return filesize($fname) / 1024;
	}
	
	/**
	 * ѹ���ļ�(zip��ʽ)
	 */
	public function tozip($items) {
		$zip = new ZipArchive();
		$zipname = date ('YmdHis', time());
		if (!file_exists ($zipname)) {
			$zip->open ($zipname.'.zip', ZipArchive::OVERWRITE); //����һ���յ�zip�ļ�
			for($i = 0; $i < count($items);$i ++) {
				$zip->addFile($this->currentdir.'/'.$items[$i], $items[$i]);
			}
			$zip->close();
			$dw = new downloadZip($zipname.'.zip'); //�����ļ�
			$dw->getfiles();
			unlink($zipname .'.zip'); //������ɺ�Ҫ����ɾ��    
		}
	}
}
?>