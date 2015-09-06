<?php
/**
 * Login class.
 *
 * This class provides for basic login functionality, saves passwords to database and enables password recvery.
 *
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license MIT
 * @version 1.0
 * @package epesi-base
 * @subpackage user-login
 */
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

defined("_VALID_ACCESS") || die('Direct access forbidden');

class Base_User_Login extends Module {
	public $theme;

	public function construct() {
		$this->theme = $this->pack_module(Base_Theme::module_name());

		$this->theme->assign('is_logged_in', Acl::is_user());
		$this->theme->assign('is_demo', DEMO_MODE);
		if (SUGGEST_DONATION) {
			$this->theme->assign('donation_note', __('If you find our software useful, please support us by making a %s.', array('<a href="http://epe.si/cost" target="_blank">'.__('donation').'</a>')).'<br>'.__('Your funding will help to ensure continued development of this project.'));
		}

		if(Acl::is_user()) {
			if ($this->get_unique_href_variable('logout')) {
				Base_User_LoginCommon::logout();
				eval_js('document.location=\'index.php\';', false);
			}
		}
	}

	private function autologin() {
	        if(Base_User_LoginCommon::autologin()) {
	            location(array());
	            return true;
	        }
		return false;
	}

	public function indicator()
	{
		//todo-pj: Add profile link
		$indicator = array(
			'label' => Base_UserCommon::get_my_user_label(),
			'login' => Base_UserCommon::get_my_user_login()
		);


		$logout = array(
			'href' => $this->create_unique_href(array('logout'=>1)),
			'text' => __('Logout')
		);

		//todo-pj: Add in_use class to template
		$perspective = array(
			'in_use' => isset($_REQUEST['__location']) ? (CRM_FiltersCommon::$in_use===$_REQUEST['__location']) : CRM_FiltersCommon::$in_use,
			'desc' => $_SESSION['client']['filter_' . Acl::get_user()]['desc'],
			'name' => __('Perspective')
		);

		return $this->render('indicator.twig', array(
				'indicator' => $indicator,
				'logout' => $logout,
				'perspective' => $perspective
			)
		);

	}

	public function login()
	{
		if (!Acl::is_user() && Base_User_LoginCommon::is_banned()) {
			print __('You have exceeded the number of allowed login attempts.').'<br>';
			print('<a href="'.get_epesi_url().'">'.__('Host banned. Click here to refresh.').'</a>');
			return;
		}

        if($this->autologin()) return;

		$formBuilder = $this->create_form_builder(array(
			'constraints' => array(
				new \Symfony\Component\Validator\Constraints\Callback(array($this, 'validate'))
			)
		));

		//todo-pj: Add information with demo users credentials

		$formBuilder->add('login','text');
		$formBuilder->add('password','password');


		if (Base_User_LoginCommon::is_autologin_forbidden() == false) {
			$formBuilder->add('autologin','checkbox', array(
				'label'=>__('Remember me'),
				'required' => false
			));
		}

		$form = $formBuilder->getForm();


		$form->handleRequest();
		if ($form->isValid()) {
			$user = $form->get('login')->getData();
			Base_User_LoginCommon::set_logged($user);

			if (Base_User_LoginCommon::is_autologin_forbidden() == false) {
				$autologin = $form->get('autologin')->getData();
				if($autologin)
				Base_User_LoginCommon::new_autologin_id();
			}

			location(array());
		}

		$recover_href = $this->create_callback_href(array($this, 'recover_pass'));


		$this->display('login.twig', array(
			'form' => $form->createView(),
			'login_header' => __('Login'),
			'login_button_label'=>__('Login'),
			'recover_pass' => $recover_href,
			'recover_pass_label' => __('Recover password'),
            'autologin_warning' => __('Keep this box unchecked if using a public computer')
		));


	}

	public function validate($data, ExecutionContextInterface $context)
	{
		if(!Base_User_LoginCommon::submit_login(array($data['login'], $data['password'])))
			$context->addViolation(__('Login or password incorrect'));

		if (!Base_User_LoginCommon::rule_login_banned($data['login']))
			$context->addViolation(__('You have exceeded the number of allowed login attempts for this username. Try again later.'));

	}

	public function recover_pass()
	{
        if ($this->is_back()) {
            return false;
        }

		$formBuilder = $this->create_form_builder(array(
				'constraints'=>array(
					new \Symfony\Component\Validator\Constraints\Callback(array($this,'check_username_mail_valid'))
				)
			)
		);

        $formBuilder->add('username', 'text', array
            (
                'label' => __('Username'),
                'constraints' => array(
                    new \Symfony\Component\Validator\Constraints\Length(array('min' => 3, 'max' => 32))
                )
            )
        );
        $formBuilder->add('email','email',array('label'=> __('E-mail')));

        $form = $formBuilder->getForm();


        $form->handleRequest();
        if ($form->isValid()) {
            $user = $form->get('username')->getData();
            $email = $form->get('email')->getData();
            $this->submit_recover($user, $email);
            $this->display('recover.twig',array(
                'message' => __('Password reset instructions were sent.')
            ));
            return false;
        }



		$this->display('recover.twig', array(
            'form' => $form->createView(),
            'form_label' => __('Recover password'),
            'submit_label' => __('OK'),
            'cancel_label' => __('Cancel'),
            'cancel_href' => $this->create_back_href()
        ));

        return true;

	}

	public static function check_username_mail_valid($data, ExecutionContextInterface $context) {
		$username = $data['username'];
		$mail = $data['email'];
		$ret = DB::Execute('SELECT null FROM user_password p JOIN user_login u ON u.id=p.user_login_id WHERE u.login=%s AND p.mail=%s AND u.active=1',array($username, $mail));
		if ($ret->FetchRow()==false) {
			$context->addViolation(__('Username or e-mail invalid'));
		}
	}

	public function submit_recover($username, $mail) {
 		if(DEMO_MODE && $username=='admin') {
 			print('In demo you cannot recover \'admin\' user password. If you want to login please type \'admin\' as password.');
			return false;
 		}

		$user_id = Base_UserCommon::get_user_id($username);
		DB::Execute('DELETE FROM user_reset_pass WHERE created_on<%T',array(time()-3600*2));

		if($user_id===false) {
			print('No such user!');
			return false;
		}
		$hash = md5($user_id.''.openssl_random_pseudo_bytes(100));
		DB::Execute('INSERT INTO user_reset_pass(user_login_id,hash_id,created_on) VALUES (%d,%s,%T)',array($user_id, $hash,time()));

		$subject = __('Password recovery');
		$message = __('A password recovery for the account with the e-mail address %s has been requested.',array($mail))."\n\n".
				   __('If you want to reset your password, visit the following URL:')."\n".
				   get_epesi_url().'/modules/Base/User/Login/reset_pass.php?hash='.$hash."\n".
				   __('or just ignore this message and your login and password will remain unchanged.')."\n\n".
				   __('If you did not use the Password Recovery form, inform your administrator about a potential unauthorized attempt to login using your credentials.')."\n\n".
				   __('This e-mail was generated automatically and you do not need to respond to it.');
		$sendMail = Base_MailCommon::send_critical($mail, $subject, $message);

		return true;
	}

}
?>
