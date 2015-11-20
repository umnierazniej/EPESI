<?php
/**
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license MIT
 * @version 1.0
 * @package epesi-apps
 * @subpackage shoutbox
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Apps_Shoutbox extends Module {

	public function body() {
		/* Do not delete anything - all messages are kept in history
		// to allow delete by admin uncomment below lines
		// if i am admin add "clear shoutbox" actionbar button
		if(Base_AclCommon::i_am_admin())
			Base_ActionBarCommon::add('trash',__('Clear shoutbox'),$this->create_callback_href(array($this,'delete_all')));
		*/
		Base_ActionBarCommon::add('caret-left',__('Back'),$this->create_back_href());
		if ($this->is_back()) {
			$x = ModuleManager::get_instance('/Base_Box|0');
			if (!$x)
				trigger_error('There is no base box module instance', E_USER_ERROR);
			$x->pop_main();
			return;
		}
		
		$tb = $this->init_module(Utils_TabbedBrowser::module_name());
		$tb->set_tab(__('Chat'), array($this,'chat'),true,null);
		$tb->set_tab(__('History'), array($this,'history'),null);
		$this->display_module($tb);
    }
	
	public function history($uid=null) {	
		$th = $this->init_module(Base_Theme::module_name());
		$th->assign('header', __('Shoutbox History'));

		$qf = $this->init_module(Libs_QuickForm::module_name());

  	    if(ModuleManager::is_installed('CRM_Contacts')>=0) {
       	    $emps = DB::GetAssoc('SELECT l.id,'.DB::ifelse('cd.f_last_name!=\'\'',DB::concat('cd.f_last_name',DB::qstr(' '),'cd.f_first_name'),'l.login').' as name FROM user_login l LEFT JOIN contact_data_1 cd ON (cd.f_login=l.id AND cd.active=1) WHERE l.active=1 ORDER BY name');
	    } else
   		    $emps = DB::GetAssoc('SELECT id,login FROM user_login WHERE active=1 ORDER BY login');
   		$qf->addElement('select','user',__('User'),array('all'=>'['.__('All').']')+$emps);
   		$qf->addElement('datepicker','from_date',__('From'));
   		$qf->addElement('datepicker','to_date',__('To'));
   		$qf->addElement('text','search',__('Search for'));
		$qf->addElement('submit','submit_button',__('Filter'));
	    
	    $to_date = & $this->get_module_variable('to_date');
	    $from_date = & $this->get_module_variable('from_date');
		$user = & $this->get_module_variable('to',"all");
		$qf->setDefaults(array('from_date'=>$from_date,'to_date'=>$to_date,'user'=>$uid));

		//if submited
		if($qf->validate()) {
			$from_date = $qf->exportValue('from_date');
			$to_date = $qf->exportValue('to_date');
			$user = $qf->exportValue('user');
			$search_word = $qf->exportValue('search');
		}

	    $qf->assign_theme('form', $th);
		
		$uid = is_numeric($user)?$user:null;
		$date_where = '';
		if($from_date)
		    $date_where .= 'AND posted_on>='.DB::DBDate($from_date);
		if($to_date)
		    $date_where .= 'AND posted_on<='.DB::DBDate($to_date);
		if (isset($search_word) && $search_word) {
			$search_word = explode(' ',$search_word);
			foreach ($search_word as $word) {
				if ($word) $date_where .= ' AND message '.DB::like().' '.DB::Concat(DB::qstr('%'),DB::qstr(htmlspecialchars($word,ENT_QUOTES,'UTF-8')),DB::qstr('%'));
			}
		}

		$gb = $this->init_module(Utils_GenericBrowser::module_name(),null,'shoutbox_history');

		$gb->set_table_columns(array(
						array('name'=>__('From'),'width'=>10),
						array('name'=>__('To'),'width'=>10),
						array('name'=>__('Message'),'width'=>64),
						array('name'=>__('Date'),'width'=>16)
						));

        // $gb->set_default_order(array(__('Date')=>'DESC'));

        $myid = Base_AclCommon::get_user();
		$where = '('.($uid?'(base_user_login_id='.$myid.' AND to_user_login_id='.$uid.') OR (base_user_login_id='.$uid.' AND to_user_login_id='.$myid.') OR (to_user_login_id is null AND base_user_login_id='.$uid.')':'to_user_login_id is null OR to_user_login_id='.$myid.' OR base_user_login_id='.$myid).')'.$date_where;
		$query = 'SELECT base_user_login_id, to_user_login_id, message, posted_on FROM apps_shoutbox_messages WHERE '.$where.' ORDER BY posted_on DESC';
        $query_qty = 'SELECT count(id) FROM apps_shoutbox_messages WHERE '.$where;

		$ret = $gb->query_order_limit($query, $query_qty);

        if($ret)
			while(($row=$ret->FetchRow())) {
				$ulogin = Base_UserCommon::get_user_label($row['base_user_login_id']);
				if($row['to_user_login_id']!==null)
    				$tologin = Base_UserCommon::get_user_label($row['to_user_login_id']);
    		    else
    		        $tologin = '['.__('All').']';
                $gb->add_row(
					'<span class="author">'.$ulogin.'</span>',
					'<span class="author">'.$tologin.'</span>',
					array('value'=>Utils_BBCodeCommon::parse($row['message']), 'overflow_box'=>false),
					Base_RegionalSettingsCommon::time2reg($row['posted_on'])
				);
			}

		$th->assign('messages',$this->get_html_of_module($gb));
		$th->display();
            return true;
	}

	//delete_all callback (on "clear shoutbox" button)
	public function delete_all() {
		DB::Execute('DELETE FROM apps_shoutbox_messages');
	}

	public function applet($conf, & $opts) {
		$opts['go'] = true;
		$this->chat();
    }
    
    public function chat($big=false,$uid=null) {
		$to = & $this->get_module_variable('to',"all");
		eval_js('shoutbox_uid="'.$to.'"');
		if(Base_AclCommon::is_user()) {
			//initialize HTML_QuickForm
			$form_builder = $this->create_form_builder(array());
			$qf = $this->init_module(Libs_QuickForm::module_name());

            $myid = Base_AclCommon::get_user();
//        	if(Base_User_SettingsCommon::get('Apps_Shoutbox','enable_im') && ModuleManager::is_installed('Tools_WhoIsOnline')>=0) {
//				$online = Tools_WhoIsOnlineCommon::get_ids();
//				if($online) {
//					if(ModuleManager::is_installed('CRM_Contacts')>=0) {
//						$emps = DB::GetAssoc('SELECT l.id,'.DB::Concat(DB::qstr("* "),DB::ifelse('cd.f_last_name!=\'\'',DB::concat('cd.f_last_name',DB::qstr(' '),'cd.f_first_name',DB::qstr(' ('),'l.login',DB::qstr(')')),'l.login')).' as name FROM user_login l LEFT JOIN contact_data_1 cd ON (cd.f_login=l.id AND cd.active=1) LEFT JOIN base_user_settings us ON (us.user_login_id=l.id AND module=\'Apps_Shoutbox\' AND variable=\'enable_im\') WHERE l.active=1 AND l.id!=%d AND (us.value=%s OR us.value is '.($adm?'':'not ').'null) AND l.id IN ('.implode(',',$online).') ORDER BY name',array($myid,serialize(1)));
//					} else
//				} else $emps = array();
//			} else $emps = array();

			$adm = Base_User_SettingsCommon::get_admin('Apps_Shoutbox','enable_im');
			$emps = DB::GetAssoc('SELECT l.id,'.DB::Concat(DB::qstr("* "),'l.login').' FROM user_login l LEFT JOIN base_user_settings us ON (us.user_login_id=l.id AND module=\'Apps_Shoutbox\' AND variable=\'enable_im\') WHERE l.active=1 AND l.id!=%d AND (us.value=%s OR us.value is '.($adm?'':'not ').'null) ORDER BY l.login',array($myid,serialize(1)));
			$form_builder->add('shoutbox_to', 'autocomplete', array(
					'label' => __('To'),
					'url' => $this->create_ajax_callback_url(array($this->get_type() . 'Common', 'user_search')),
			));

			$form_builder->get('shoutbox_to')->addViewTransformer(new \Symfony\Component\Form\CallbackTransformer(function ($value) {
						$result = array();
						if(!$value)
							return $result;

						$result[] = $value;
						$result['_labels'][] = $value;
						return $result;
					}, function ($value) {
						return $value;
					})
			);

			$form_builder->add('post','textarea', array(

			));

			$form = $form_builder->getForm();

//        	if(!Base_User_SettingsCommon::get('Apps_Shoutbox','enable_im'))
//        	    $qf->freeze(array('shoutbox_to'));

			$form->handleRequest();
			if ($form->isValid()) {
				$data = $form->getData();

				$to = $data['shoutbox_to'];
				//get msg from post group
				$msg = Utils_BBCodeCommon::optimize($data['post']);
				//get logged user id
				$user_id = Base_AclCommon::get_user();

				//insert to db
				DB::Execute('INSERT INTO apps_shoutbox_messages(message,base_user_login_id,to_user_login_id) VALUES(%s,%d,%d)',array(htmlspecialchars($msg,ENT_QUOTES,'UTF-8'),$user_id,is_numeric($to)?$to:null));

				location();
			}
		} else {
			print(__('Please log in to post message').'<br>');
			return;
		}

//		eval_js_once('shoutbox_refresh'.($big?'_big':'').' = function(){if(!$(\'shoutbox_board'.($big?'_big':'').'\')) return;'.
//			'new Ajax.Updater(\'shoutbox_board'.($big?'_big':'').'\',\'modules/Apps/Shoutbox/refresh.php\',{method:\'get\', parameters: { uid: shoutbox_uid }});'.
//			'};setInterval(\'shoutbox_refresh'.($big?'_big':'').'()\','.($big?'10000':'30000').')');
//		eval_js('shoutbox_refresh'.($big?'_big':'').'()');

		$this->display('chat_form.twig', array(
				'form' => $form->createView(),
				'big' => $big,
				'messages' => $this->get_messages()
		));
	}

	public function caption() {
		return __('Shoutbox');
	}

	public function get_messages()
	{
		$messages = array();

		$myid = Base_AclCommon::get_user();
		$uid = (isset($_GET['uid']) && is_numeric($_GET['uid']))?$_GET['uid']:null;

		$arr = DB::GetAll('SELECT asm.base_user_login_id as from_login_id, asm.to_user_login_id as to_login_id,asm.message, asm.posted_on FROM apps_shoutbox_messages asm WHERE '.($uid?'(base_user_login_id='.$myid.' AND to_user_login_id='.$uid.') OR (base_user_login_id='.$uid.' AND to_user_login_id='.$myid.')':'to_user_login_id is null OR to_user_login_id='.$myid.' OR base_user_login_id='.$myid).' ORDER BY asm.posted_on DESC LIMIT 20');
		foreach($arr as $row) {
			$daydiff = floor((time()-strtotime($row['posted_on']))/86400);
			switch (true) {
				case ($daydiff<1): $fcolor = '#000000'; break;
				case ($daydiff<3): $fcolor = '#444444'; break;
				case ($daydiff<7): $fcolor = '#888888'; break;
				default : $fcolor = '#AAAAAA';
			}
			$user_label = Apps_ShoutboxCommon::create_write_to_link($row['from_login_id']);
			if ($row['to_login_id'])
				$user_label .= ' -> '.Apps_ShoutboxCommon::create_write_to_link($row['to_login_id']);

			$message = (($row['to_login_id']==$myid && $uid===null)?'<b>':'').Utils_BBCodeCommon::parse($row['message']).(($row['to_login_id']==$myid && $uid===null)?'</b>':'');
			$time = Base_RegionalSettingsCommon::time2reg($row['posted_on'],2);
			$color = array('default','primary','success','danger','warning','info')[$row['from_login_id'] % 6];
			$messages[] = array('message'=>$message, 'time' => $time, 'color' => $color, 'user_label' => $user_label);
		}
		return $messages;
	}
}

?>
