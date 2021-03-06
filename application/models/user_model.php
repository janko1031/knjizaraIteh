<?php
class User_model extends CI_Model
{


    public function __construct()
    {
        parent::__construct();
    }

function vratiKorisnika($id)
      {
       $this->db->select('*'); 
       $this->db->from('users');   
       $this->db->where('id', $id);
       return $this->db->get()->result();

      }

    function vrati_knjigeKorisnika($user_id)
    {
        $data = array(
          'user_id' => $user_id ,
         
          );     
        $this->db->select('*');
        $this->db->from('korpa');

        $this->db->join('knjige', 'korpa.knjiga_id = knjige.id_knjige', 'left');
        $this->db->join('slike', 'knjige.id_knjige = slike.knjiga_id', 'left');
        $this->db->where($data); 
        $query = $this->db->get();
        return $query->result();
    }
    function vrati_brojKnjiga($user_id)
    {
        $data = array(
          'user_id' => $user_id ,
          
          ); 
        $this->db->select('*');
        $this->db->from('korpa');

        $this->db->join('knjige', 'korpa.knjiga_id = knjige.id_knjige', 'left');
        $this->db->where($data); 
        return $this->db->count_all_results();
    }
    function vrati_UkCenu($user_id)
    {
       $data = array(
          'user_id' => $user_id ,
          
          );    
       $cena=0;
       $this->db->select('cena');
       $this->db->from('korpa');

       $this->db->join('knjige', 'korpa.knjiga_id = knjige.id_knjige', 'left');
       $this->db->where($data); 
       $query = $this->db->get();
       foreach ($query->result() as $result) {
        $cena+=$result->cena;
    }
    return $cena;

}

public function proveriEmail(){
    $this->load->library('form_validation');
    $email = $this->input->post('email');


    $this->db->where('email',$email);
    $query = $this->db->get('users');
    if ($query->num_rows() > 0){
        return true;
        
    }
    else{
        return false;
    }

}




}

?>
