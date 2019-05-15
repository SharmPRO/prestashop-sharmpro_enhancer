<?php
class AdminOrdersController extends AdminOrdersControllerCore
{
    /*
    * module: spro_enhancer
    * date: 2019-05-14 09:37:19
    * version: 0.0.1
    */
    public function __construct()
    {
        parent::__construct();
        if(!Configuration::get('SPRO_ENHANCER_LIVE_MODE', false)){
            return;
        }
        $leave = true;
        foreach(array(
            'SPRO_ENHANCER_BO_ORDER_LIST_CARRIER',
            'SPRO_ENHANCER_BO_ORDER_LIST_GUEST',
            'SPRO_ENHANCER_BO_ORDER_LIST_NOTE',
            'SPRO_ENHANCER_BO_ORDER_LIST_MESSAGE',
            'SPRO_ENHANCER_BO_ORDER_LIST_REMOVE_REFERENCE'
            ) as $k){
                ${$k} = Configuration::get($k, false);
                if( ${$k} === true || (int)${$k} !== 0 ) {
                    $leave = false;
                }        
        }
        if($leave === true){
            return;
        }

        $this->_select .= ', IF(carrier.`name` = "0", "-", carrier.`name`) AS `carriername`';
        
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'carrier` carrier ON a.id_carrier = carrier.id_carrier';
        
        $this->_join .= ' LEFT JOIN '._DB_PREFIX_.'customer_thread ct ON ct.id_order=a.id_order';
        
        if((int)$SPRO_ENHANCER_BO_ORDER_LIST_NOTE === 1){  
            $this->_select .= ', c.note AS `note`';
        } else {
            $this->_select .= ', address.other AS `note`';
        }
        
        $this->_select .= ', (SELECT message FROM '._DB_PREFIX_.'customer_message cm WHERE cm.id_customer_thread=ct.id_customer_thread AND ct.status="open" ORDER BY cm.date_upd DESC LIMIT 1) AS `message`';
        
        $toAddArray = array();
        if($SPRO_ENHANCER_BO_ORDER_LIST_CARRIER){
            $toAddArray['carriername'] = array(
                    'title' => $this->l('Carrier'),
                    'type' => 'text',
                    'align' => 'text-left',
                    'filter_key' => 'carrier!name',
                    'filter_type' => 'text'
            );
        }
        if(count($toAddArray)>0){
            $this->fields_list = $this->insertIntoFieldList('payment',$toAddArray);
        }
        
        $toAddArray = array();
        if($SPRO_ENHANCER_BO_ORDER_LIST_GUEST){
            $toAddArray['is_guest'] = array(
                'title' => $this->l('Guest'),
                'type' => 'boolean',
                'align' => 'text-center',
            );
        }   
        if(count($toAddArray)>0){
            $this->fields_list = $this->insertIntoFieldList('new',$toAddArray);
        }
        
        $toAddArray = array();
        if($SPRO_ENHANCER_BO_ORDER_LIST_NOTE){
            $toAddArray['note'] = array(
                'title' => $this->l('Note'),
                'havingFilter' => true,
            );
        }
        if($SPRO_ENHANCER_BO_ORDER_LIST_MESSAGE){
            $toAddArray['message'] = array( // aggiunta della colonna messaggio - nominata message
                'title' => $this->l('Message'),
                'havingFilter' => true,
            );
        }
        if(count($toAddArray)>0){
            $this->fields_list = $this->insertIntoFieldList('date_add',$toAddArray);
        }
        if($SPRO_ENHANCER_BO_ORDER_LIST_REMOVE_REFERENCE){
            unset($this->fields_list['reference']);
        }
        
    }
    /*
    * module: spro_enhancer
    * date: 2019-05-14 09:37:19
    * version: 0.0.1
    */
    private function insertIntoFieldList($toAddAfter,$toAddArray){
        $keys = array_keys( $this->fields_list );
        $index = array_search( $toAddAfter, $keys , true);
        $pos = false === $index ? count( $this->fields_list ) : $index + 1;
        return array_merge( array_slice( $this->fields_list, 0, $pos ), $toAddArray, array_slice( $this->fields_list, $pos ) );
    }
    /*
    public function setIsGuest($content,$params){
        $content = ($params['is_guest'] == 1) ? 'Yes' : '';
        return $content;
    }
    */
    /*
    public function setCarrierBadge($content,$params){
        if($params['carrier_badge'] == 1){
            $content = '<span class="badge badge-success">'.$content.'</span>';
        }
        return $content;
    }
    */
}