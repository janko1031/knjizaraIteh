<?php

class Knjiga_model extends CI_Model {
    /* var $naziv ;
      var $autor ;
      var $zanr ;
      var $godina ;
      var $izdavac;
      var $opis ;
      var $br_strana;
      var $cena ;
      var $kolicina ;
     */

    public function __construct() {
        parent::__construct();
    }

    function vratiKnjige() {
        return $this->db->get('knjige')->result(); // vraca sve knjige
    }

    function vratiKolicinu($id) {
        $this->db->select('kolicina');
        $this->db->from('knjige');
        $this->db->where('id_knjige', $id);
        $row = $this->db->get()->result();
        foreach ($row as $kol) {
            $kolicina = $kol->kolicina;
        }
        return $kolicina; //vraca broj dostupnih knjiga na skladistu
    }

    function povecajKolicinu($id_knjige) {
        //$id_knjige = $this->input->post('id_knjige');

        $kolicina = $this->knjiga_model->vratiKolicinu($id_knjige);    //vraca broj dostupnih knjiga na skladistu

        $kolicina+=1;
        $data = array(
            'kolicina' => $kolicina,
        );

        $this->db->where('id_knjige', $id_knjige); //azurira polje kolicina u tabeli knjige
        $this->db->update('knjige', $data);
    }

    function smanjiKolicinu($id_knjige) {
        // $id_knjige = $this->input->post('id_knjige');
        $kolicina = $this->knjiga_model->vratiKolicinu($id_knjige);

        $kolicina-=1;
        $data = array(
            'kolicina' => $kolicina,
        );

        $this->db->where('id_knjige', $id_knjige);
        $this->db->update('knjige', $data);
    }

    function dodajknjigu() {

        $knjiga = new Knjiga_model;
        $knjiga->naziv = $this->input->post('naziv');
        $knjiga->autor = $this->input->post('autor');
        $knjiga->zanr = $this->input->post('zanr');
        $knjiga->godina_izdanja = $this->input->post('godina_izdanja');
        $knjiga->izdavac = $this->input->post('izdavac');
        $knjiga->opis = $this->input->post('opis');
        $knjiga->br_strana = $this->input->post('br_strana');
        $knjiga->cena = $this->input->post('cena');
        $knjiga->kolicina = $this->input->post('kolicina');


        $this->db->insert('knjige', $knjiga);
    }

    function dodajSliku() {

      $id= $this->db->insert_id();

        $query = $this->db->get_where('knjige', array('id_knjige' => $id))->result();
        foreach ($query as $row) {
            $id_knjige = $row->id_knjige;
        }

        $config['upload_path'] = './assets/img/knjige/';
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size'] = '1024';
        $config['max_width'] = '1440';
        $config['max_height'] = '990';

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload()) {
            $data = array('data' => $this->upload->display_errors());            

            $this->load->view('template', array(
            "folder" => "admin",
            "page" => "unos_knjige",
            "broj" => 123,
            "user" => $this->ion_auth->user()->row(),
            "title" => "Unos nove knjige",
             "data" =>$data,
            ));
        } else {

            //$this->upload->display_errors() je niz pravimo jos jedan niz $data
            //koji ima jedan clan, a taj clan je niz.


            $data = array('data' => $this->upload->data()); // prva opcija dva foreacha
            foreach ($data as $array) {

                $img_name = $array['file_name'];
            }
            /* $data1= $this->upload->data();  // opcija DVA

              $img = $data1['file_name']; */

            $this->db->set('img_name', $img_name);
            $this->db->set('knjiga_id', $id_knjige);
            $this->db->insert('slike');
            redirect('katalog/prikazi_katalog', 'refresh');

        }
    }

    function vrati_podatke_za_katalog($limit, $start) {
        $this->db->select('*');
        $this->db->from('slike');
        $this->db->limit($limit, $start);
        $this->db->join('knjige', 'slike.knjiga_id = knjige.id_knjige', 'left');
        $this->db->order_by("br_strana", "desc");
        $query = $this->db->get();

        return $query->result();
    }

    function vrati_podatkeZaNaslovniKatalog() {
        $this->db->select('*');
        $this->db->from('slike');
        $this->db->limit(4);
        $this->db->join('knjige', 'slike.knjiga_id = knjige.id_knjige', 'left');
        $this->db->like('zanr', 'drama');
        $this->db->or_like('zanr', ', drama');

        $this->db->order_by("naziv", "desc");
        $query = $this->db->get();

        return $query->result();
    }

    public function broj_rezultata() {

        return $this->db->count_all("knjige");
    }

    function vrati_knjigu($id) {

        $this->db->select('*');
        $this->db->from('knjige');
        $this->db->join('slike', 'slike.knjiga_id = knjige.id_knjige', 'left');
        $this->db->where('id_knjige', $id);
        $query = $this->db->get();

        return $query->result();
    }

    function vrati_slicneKnjige($id, $zanr, $autor) {


        $this->db->select('*');
        $this->db->from('knjige');
        $this->db->join('slike', 'slike.knjiga_id = knjige.id_knjige', 'left');
        $this->db->where('id_knjige', $id);

        $this->db->like('zanr', $zanr);
        $this->db->or_like('autor', $autor);
        $this->db->or_like('zanr', $zanr);


        $this->db->limit(6);
        $this->db->order_by("zanr", "desc");
        $query = $this->db->get();

        return $query->result();
    }

    function vrati_recenzije($id) {

        $this->db->select('recenzije.*,users.*');
        $this->db->from('recenzije');
        $this->db->join('knjige', 'recenzije.knjiga_id = knjige.id_knjige', 'left');
        $this->db->join('users', 'recenzije.user_id = users.id', 'left');
        $this->db->where('id_knjige', $id);
        $query = $this->db->get();

        return $query->result();
    }

    function pretraziPoCeni() {

        $od = $this->input->post('cenaOD');
        $do = $this->input->post('cenaDO');
        $this->db->select('*');
        $this->db->from('knjige');
        $this->db->join('slike', 'slike.knjiga_id = knjige.id_knjige', 'left');
        $this->db->where("cena BETWEEN $od AND $do");


        $query = $this->db->get();

        return $query->result();
    }

    function brojRezultataPretrage($keyword) {

        $this->db->like('naziv', $keyword);
        $this->db->or_like('autor', $keyword);
        $this->db->or_like('naziv', $keyword);
        $this->db->or_like('izdavac', $keyword);
        $this->db->from('knjige');
        $query = $this->db->get();
        return $query->num_rows();
    }

    function pretrazi($keyword, $page) {

        $this->db->like('naziv', $keyword);
        $this->db->or_like('autor', $keyword);

        $this->db->or_like('naziv', $keyword);
        $this->db->or_like('izdavac', $keyword);
        $this->db->from('knjige');

        $this->db->limit(8, $page);
        $this->db->join('slike', 'slike.knjiga_id = knjige.id_knjige', 'left');
        $query = $this->db->get();
        return $query->result();
    }

    function brojPoZanru($zanr) {

        $this->db->like('zanr', $zanr);
        $this->db->or_like('zanr', ', ' . $zanr);
        $this->db->from('knjige');
        $query = $this->db->get();
        return $query->num_rows();
    }

    function filtrirajPoZanru($zanr) {

        $this->db->like('zanr', $zanr);
        $this->db->or_like('zanr', ', ' . $zanr);
        $this->db->from('knjige');

        //$this->db->limit(8, $page);
        $this->db->join('slike', 'slike.knjiga_id = knjige.id_knjige', 'left');
        $query = $this->db->get();
        return $query->result();
    }

    function vratiSveZanrove() {

        return $this->db->get('zanr')->result(); // vraca sve knjige
    }

    function vratiProdajuPoZanru($zanr) {

        $this->db->select('*');
        $this->db->from('kupljene_knjige');
        $this->db->join('knjige', 'knjige.id_knjige = kupljene_knjige.knjiga_id', 'left');
        $this->db->where('zanr_id', $zanr);
        $query = $this->db->get();
        return $query->num_rows();
    }

    function vratiProdajuIzdavaca($izdavac) {

        $this->db->select('*');
        $this->db->from('kupljene_knjige');
        $this->db->join('knjige', 'knjige.id_knjige = kupljene_knjige.knjiga_id', 'left');
        $this->db->where('knjige.izdavac', $izdavac);
        //$this->db->where('datum_kupovine BETWEEN "'. date('Y-m-d', strtotime($datumOD)). '" and "'. date('Y-m-d', strtotime($datumDO)).'"');
        $query = $this->db->get();
        return $query->num_rows();
    }

    function vratiPrihodPoGodinama($datumOD, $datumDO, $izdavac) {

        $this->db->select('*');
        $this->db->from('kupljene_knjige');
        $this->db->join('knjige', 'knjige.id_knjige = kupljene_knjige.knjiga_id', 'left');
        $this->db->where('knjige.izdavac', $izdavac);
        $this->db->where('datum_kupovine BETWEEN "' . date('Y-m-d', strtotime($datumOD)) . '" and "' . date('Y-m-d', strtotime($datumDO)) . '"');
        $query = $this->db->get();
        $prihod = 0;
        foreach ($query->result() as $row) {
            $prihod+=$row->cena;
        }
        return $prihod;
    }

}

?>
