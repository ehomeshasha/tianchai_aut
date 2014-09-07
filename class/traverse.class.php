<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once AUT_PATH."/class/downloadZip.class.php";
/**
 * 遍历目录，打包成zip格式
 */
class traverseDir {
	public $currentdir; //当前目录
	public $filename; //文件名
	public $fileinfo; //用于保存当前目录下的所有文件名和目录名以及文件大小
	public function __construct($downloadDir) {
		$this->currentdir = $downloadDir; //返回当前目录
	}
	//遍历目录
	public function scandir($filepath) {
		if (is_dir($filepath)) {
			$arr = scandir($filepath);
			foreach ($arr as $k => $v) {
				$this->fileinfo[$v][] = $this->getfilesize($v);
			}
		} else {
			echo "<script>alert('当前目录不是有效目录或目录不存在');</script>";
		}
	}
	/**
	 * 返回文件的大小
	 *
	 * @param string $filename 文件名
	 * @return 文件大小(KB)
	 */
	public function getfilesize($fname) {
		return filesize($fname);
	}
	
	/**
	 * 压缩文件(zip格式)
	 */
	public function tozip($items) {
		$zip = new ZipArchive();
		$zipname = "data/".date ('YmdHis', time()).".zip";
		if (!file_exists ($zipname)) {
			//echo $zipname;
			$zip->open ($zipname, ZIPARCHIVE::CREATE); //创建一个空的zip文件
			
			echo "file=".!file_exists ($zipname);
			for($i = 0; $i < count($items);$i ++) {
				//echo "<h6>".$this->currentdir.'/'.$items[$i]."</h6>";
				$zip->addFile($this->currentdir.'/'.$items[$i], $items[$i]);
			}
			$zip->close();
			$dw = new downloadZip($zipname); //下载文件
			$dw->getfiles();
			//unlink($zipname); //下载完成后要进行删除
			   
		}
		//$zip->close();
	}
}
?>