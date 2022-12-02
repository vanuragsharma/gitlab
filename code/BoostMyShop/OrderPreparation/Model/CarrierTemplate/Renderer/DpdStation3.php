<?php

namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer;

class DpdStation3 extends RendererAbstract
{
    protected $_lineReturn = "\r\n";

    public function getShippingLabelFile($ordersInProgress, $carrierTemplate){

        $content = '$VERSION=110'.$this->_lineReturn;


        foreach($ordersInProgress as $orderInProgress)
        {
            $content .= $this->getOrderLine($orderInProgress);
        }

        return $content;
    }

    public function getShippingLabelData($ordersInProgress, $carrierTemplate)
    {
        $shippingLabelData = ['file' => false, 'trackings' => []];
        $content = '$VERSION=110'.$this->_lineReturn;

        foreach($ordersInProgress as $orderInProgress)
            $content .= $this->getOrderLine($orderInProgress);

        $shippingLabelData['file'] = $content;

        return $shippingLabelData;
    }

    protected function getOrderLine($orderInProgress)
    {
        $line = '';

        $streetLines = $orderInProgress->getOrder()->getShippingAddress()->getStreet();
        $streetLines = str_split(implode(' ', $streetLines), 34);

        $shippingAddress = $orderInProgress->getOrder()->getShippingAddress();
        $customerName = $shippingAddress->getfirstname().' '.$shippingAddress->getlastname();

        $line .= str_pad($orderInProgress->getOrder()->getincrement_id(), 35, " ", STR_PAD_LEFT);       //1 reference client no 1
        $line .= str_pad("", 2, " ", STR_PAD_LEFT);                                                      //2 filler
        $line .= str_pad((int)$orderInProgress->getip_total_weight() * 100, 8, "0", STR_PAD_LEFT);      //3 weight

        //Adressse destinataire
        $line .= str_pad("", 15, " ", STR_PAD_LEFT);                                                     //4 filler
        $line .= str_pad($customerName, 35, " ", STR_PAD_LEFT);                                          //5 nom destinataire
        $line .= str_pad(isset($streetLines[1]) ? $streetLines[1] : '', 35, " ", STR_PAD_LEFT);             //6 addresse 1
        $line .= str_pad(isset($streetLines[2]) ? $streetLines[2] : '', 35, " ", STR_PAD_LEFT);             //7 addresse 2
        $line .= str_pad(isset($streetLines[3]) ? $streetLines[3] : '', 35, " ", STR_PAD_LEFT);             //8 addresse 3
        $line .= str_pad(isset($streetLines[4]) ? $streetLines[4] : '', 35, " ", STR_PAD_LEFT);             //9 addresse 4
        $line .= str_pad(isset($streetLines[5]) ? $streetLines[5] : '', 35, " ", STR_PAD_LEFT);             //10 addresse 5
        $line .= str_pad($shippingAddress->getpostcode(), 10, " ", STR_PAD_LEFT);                        //11 postcode
        $line .= str_pad($shippingAddress->getcity(), 35, " ", STR_PAD_LEFT);                            //12 city
        $line .= str_pad("", 10, " ", STR_PAD_LEFT);                                                     //13 filler
        $line .= str_pad(isset($streetLines[0]) ? $streetLines[0] : '', 35, " ", STR_PAD_LEFT);          //14 rue
        $line .= str_pad("", 10, " ", STR_PAD_LEFT);                                                     //15 filler
        $line .= str_pad($shippingAddress->getcountry_code(), 3, " ", STR_PAD_LEFT);                     //16 country
        $line .= str_pad($shippingAddress->gettelephone(), 20, " ", STR_PAD_LEFT);                       //17 telephone

        //Adresse expéditeur
        $line .= str_pad("", 25, " ", STR_PAD_LEFT);                                                     //18 filler
        $line .= str_pad("E-syCommerce", 35, " ", STR_PAD_LEFT);                                         //19 nom expéditeur
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //20 complement address
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //21 complement address
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //22 complement address
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //23 complement address
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //24 complement address
        $line .= str_pad("68720", 10, " ", STR_PAD_LEFT);                                                //25 code postal
        $line .= str_pad("Heidwiller", 35, " ", STR_PAD_LEFT);                                           //26 ville
        $line .= str_pad("", 10, " ", STR_PAD_LEFT);                                                     //27 filler
        $line .= str_pad("3 riedweg", 35, " ", STR_PAD_LEFT);                                            //28 rue
        $line .= str_pad("", 10, " ", STR_PAD_LEFT);                                                     //29 filler
        $line .= str_pad("FR", 3, " ", STR_PAD_LEFT);                                                    //30 pays
        $line .= str_pad("0800746935", 20, " ", STR_PAD_LEFT);                                           //31 telephone
        $line .= str_pad("", 10, " ", STR_PAD_LEFT);                                                     //32 filler
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //33 instructions livraison
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //34 instructions livraison
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //35 instructions livraison
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //36 instructions livraison

        //expedition
        $line .= str_pad(date('d/m/Y'), 10, " ", STR_PAD_LEFT);                                          //37 date
        $line .= str_pad("12201", 8, " ", STR_PAD_LEFT);                                                 //38 no compte dpd charger
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //39 code a barre
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //40 reference client no 2
        $line .= str_pad("", 29, " ", STR_PAD_LEFT);                                                     //41 filler
        $line .= str_pad("", 9, "0", STR_PAD_LEFT);                                                     //42 valeur déclarée
        $line .= str_pad("", 8, " ", STR_PAD_LEFT);                                                      //43 filler
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //44 reference client no 3
        $line .= str_pad("", 1, " ", STR_PAD_LEFT);                                                      //45 filler
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //46 numero consolidation
        $line .= str_pad("", 10, " ", STR_PAD_LEFT);                                                     //47 filler
        $line .= str_pad("contact@easypiscine.fr", 80, " ", STR_PAD_LEFT);                               //48 email expediteur
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //49 gsm expediteur
        $line .= str_pad($orderInProgress->getOrder()->getcustomer_email(), 80, " ", STR_PAD_LEFT);      //50 email destinataire
        $line .= str_pad($shippingAddress->gettelephone(), 35, " ", STR_PAD_LEFT);                       //51 gsm destinataire
        $line .= str_pad("", 96, " ", STR_PAD_LEFT);                                                     //52 filler
        $line .= str_pad("", 8, " ", STR_PAD_LEFT);                                                      //53 identifant point relais
        $line .= str_pad("", 113, " ", STR_PAD_LEFT);                                                    //54 filler
        $line .= str_pad("", 2, " ", STR_PAD_LEFT);                                                      //55 consolition type
        $line .= str_pad("", 2, " ", STR_PAD_LEFT);                                                      //56 consolition attribut
        $line .= str_pad("", 1, " ", STR_PAD_LEFT);                                                      //57 filler
        $line .= str_pad("+", 1, " ", STR_PAD_LEFT);                                                     //58 predict
        $line .= str_pad($shippingAddress->getlastname(), 35, " ", STR_PAD_LEFT);                        //59 nom du contact
        $line .= str_pad("", 10, " ", STR_PAD_LEFT);                                                     //60 digicode 1
        $line .= str_pad("", 10, " ", STR_PAD_LEFT);                                                     //61 digicode 2
        $line .= str_pad("", 10, " ", STR_PAD_LEFT);                                                     //62 intercom

        //Addresse retour
        $line .= str_pad("", 200, " ", STR_PAD_LEFT);                                                    //63 filler
        $line .= str_pad("", 1, " ", STR_PAD_LEFT);                                                      //64 retour
        $line .= str_pad("", 15, " ", STR_PAD_LEFT);                                                     //65 filler
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //66 nom destinataire
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //67 complement adresse 1
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //68 complement adresse 2
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //69 complement adresse 3
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //70 complement adresse 4
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //71 complement adresse 5
        $line .= str_pad("", 10, " ", STR_PAD_LEFT);                                                     //72 cp
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //73 ville
        $line .= str_pad("", 10, " ", STR_PAD_LEFT);                                                     //74 filler
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //75 rue
        $line .= str_pad("", 10, " ", STR_PAD_LEFT);                                                     //76 filler
        $line .= str_pad("", 3, " ", STR_PAD_LEFT);                                                      //77 code pays
        $line .= str_pad("", 30, " ", STR_PAD_LEFT);                                                     //78 telephone
        $line .= str_pad("", 18, " ", STR_PAD_LEFT);                                                     //79 cargo
        $line .= str_pad("", 35, " ", STR_PAD_LEFT);                                                     //80 reference client 4

        $line .= $this->_lineReturn;

        return $line;
    }

}
