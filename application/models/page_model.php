<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Page_model extends CI_Model
{
	public function submit_contact_form($post)
	{
		//Verify data.
		if(!empty($post['comments']) || empty($post['message'])) {
			$this->output->set_output("ERROR");
			return;
		}
		$this->load->library('email');
		
		//Clean up the message field.
		$message_clean = $this->input->post('message', true);
		
		//Put the message in the database.
		$this->db->insert('submissions', array(
			'type' => $post['reason'],
			'message' => $message_clean,
			'email' => $post['email'],
			'url' => $post['url'],
			'submitted' => date('Y-m-d H:i:s'),
			'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''
		));
		
		//Format the message.
		$message = "URL: {$post['url']}\n";
		$message .= "Email: {$post['email']}\n";
		$message .= "Message: {$message_clean}\n";
		
		//Set up headers and stuff.
		$this->email->from(empty($post['email']) ? 'webmaster@raidrifts.com' : $post['email']);
		$this->email->to('webmaster@raidrifts.com');
		$this->email->subject('Feedback Form Submission - '.$post['reason']);
		$this->email->message($message);
		
		//Send it.
		if($this->email->send())
			return true;
		return false;
	}
	
	public function update_page($id, $post)
	{
		if(!$this->user->admin) return false;
		
		//Put the most recent timestamp in too.
		$post['date_lastedit'] = date('Y-m-d H:i:s');
		$this->db->update('pages', $post, array('id' => $id));
		return true;
	}
	
	public function load_page($arg)
	{
		$where = is_numeric($arg) ? array('id' => $arg) : array('name' => $arg);
		
		$rs = $this->db->get_where('pages', $where);
		if($rs->num_rows() < 1) return false;
		
		$data = $rs->row();
		$rs->free_result();
		return $data;
	}
}
?>