<?php
require('FileModel.php');
require('McmModel.php');

function DownloadFile($url, $save_dir = '', $filename = '', $type = 0) 
{
	if (trim($url) == '') 
	{
		return false;
	}
	if (trim($save_dir) == '') 
	{
		$save_dir = './';
	}
	if (0 !== strrpos($save_dir, '/')) 
	{
		$save_dir.= '/';
	}
	if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) 
	{
		return false;
	}
	if ($type) 
	{
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$content = curl_exec($ch);
		curl_close($ch);
	} else
	{
		ob_start();
		readfile($url);
		$content = ob_get_contents();
		ob_end_clean();
	}
	$size = strlen($content);
	$fp2 = fopen($save_dir . $filename, 'a');
	fwrite($fp2, $content);
	fclose($fp2);
	unset($content, $url);
	return array('file_name' => $filename,'save_path' => $save_dir . $filename);
}

$id=$_POST["modelid"];
$File = new FileModel();
$filter = '{"where":{"id":$id}}';
$model_list_json = $File->objFindAll($filter);
$model_list = json_decode($model_list_json);

$mcm = new McmModel();
$name='model';
$relationName='origin';
$relations_json = $mcm->relationGet($name,$id,$relationName);
$relations = json_decode($relations_json);
for ($j = 0; $j < count($relations); $j++)
{
	$url = $relations[$j]->{'resource'}->{"url"};
	DownloadFile($url, '/root/ab/image/'.$id, basename($url), 1);
}
$url = $relations[0]->{'resource'}->{"url"};
$cam_model=$model_list[0]->{'cam_model'};
/*if($url[strlen($url)-1] != 'g' && $url[strlen($url)-1] != 'G')
{
	passthru("chmod 755 home/video2photo.sh");
	passthru("home/video2photo.sh '/home/images/'.$id");
}*/
exec("cd /root/ab && mkdir result && cd result && mkdir $id");
exec("docker exec -it hardcore_nightingale ./MVG_MVS_Pipeline.sh '/root/ab/image/'.$id '/root/ab/result/'.$id");
$data['obj']="/home/3dmodel/.$id./openMVS_result/Scene_mesh_texture.obj"
$data['mtl']="/home/3dmodel/.$id./openMVS_result/Scene_mesh_texture.mtl"
$data['texture']="/home/3dmodel/.$id./openMVS_result/Scene_mesh_texture.png"
$mcm->objPut($name,$id,$data);

?>
