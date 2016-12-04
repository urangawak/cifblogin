<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//INCLUDE KAN AUTOLOAD FACEBOOK. INI COCOK UNTUK CI KALAU PANGGIL Facebook.php bakal ga diketahui
require_once APPPATH.'libraries/Facebook/autoload.php';

class Login extends CI_Controller {

	//BUAT VARIABLE FB
	var $fb;
	
	function __construct()
	{
		parent::__construct();
		
		//LOAD HELPER URL
		$this->load->helper('url');
		//LOAD LIBRARY SESSION
		$this->load->library('session');
		
		//INISIALKAN APP_ID dan APP_SECRET
		/*
		BUAT TERLEBIH DAHULU APPLICATION FACEBOOK https://developers.facebook.com/apps/		
		*/
		$this->fb=new Facebook\Facebook([
			'app_id' => '', // APP_ID
		  	'app_secret' => '', // APP SECRET
		  	'default_graph_version' => 'v2.5', //GRAPH VERSION https://developers.facebook.com/docs/graph-api/using-graph-api/
		]);
	}

	public function index()
	{
		//CEK SESSION TOKEN DULU, INI DIBUAT PAKE CI
		$token=$this->session->userdata('fb_token');
		if(!empty($token))
		{
			//JIKA SUDAH LOGIN MAKA LANJUT KE HALAMAN INFO AKUN
			redirect(base_url().'index.php/login/infoakun');
		}else{
			//BIKIN HELPER UNTUK LOGIN FACEBOOK DARI SDK
			$helper=$this->fb->getRedirectLoginHelper();
			//INISIALKAN EMAIL SEBAGAI PRIMARY LOGIN, JADI AKUN FACEBOOK HARUS ADA EMAIL
			$permission=['email'];
			//INISIALKAN URL SETELAH LOGIN
			$callback=base_url().'index.php/login/callbackfb';
			//INISIALKAN PERMISSION PRIMARY LOGIN PADA CALLBACK URL
			$loginUrl=$helper->getLoginUrl($callback,$permission);
			//KIRIM DATA LOGIN URL
			$d['login']=$loginUrl;
			//LOAD VIEW LOGIN
			$this->load->view('loginview',$d);
		}		
	}
	
	function callbackfb()
	{
		//BIKIN HELPER LOGIN
		$helper = $this->fb->getRedirectLoginHelper();
		try {
		  //CEK TOKEN FACEBOOK TERLEBIH DAHULU, KALAU BERHASIL LANJUT KE VIEW AKUN
		  $accessToken = $helper->getAccessToken();
		  //BIKIN SESSION DARI CI DAN SIMPAN TOKEN FACEBOOK
		  $this->session->set_userdata('fb_token',$accessToken);
		  redirect(base_url().'index.php/login/infoakun');
		} catch(Facebook\Exceptions\FacebookResponseException $e) {		 
		  echo 'Graph returned an error: ' . $e->getMessage();
		  exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {		  
		  echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  exit;
		}		
	}
	
	function infoakun()
	{
		try {
		  //AMBIL TOKEN FACEBOOK
		  $token=$this->session->userdata('fb_token');
		  //JALANKAN FQL FACEBOOK QUERY
		  $response = $this->fb->get('/me?fields=id,name', $token);
		  //DUMP GRAPH FACEBOOK
		  $user = $response->getGraphUser();
		  //KIRIM DATA
		  $d['userinfo']=$user;
		  //LOAD VIEW INFOAKUN
		  $this->load->view('infoakun',$d);
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  echo 'Graph returned an error: ' . $e->getMessage();
		  exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  exit;
		}
	}
	
	function logout()
	{
		//HAPUS SESSION
		$this->session->unset_userdata('fb_token');
		redirect(base_url());
	}
}
