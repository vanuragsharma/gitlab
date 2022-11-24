<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MyFatoorah\MyFatoorahPaymentGateway\Model\Config\Source;

/**

 * Class GatewayAction

 */
class GatewayAction implements \Magento\Framework\Option\ArrayInterface {

    /**

     * {@inheritdoc}

     */
    public function toOptionArray() {
        return array(
            ['value' => 'myfatoorah', 'label' => 'MyFatoorah'],
            ['value' => 'kn', 'label' => 'KNET'],
            ['value' => 'vm', 'label' => 'VISA/MASTER'],
            ['value' => 'md', 'label' => 'MADA'],
            ['value' => 'b', 'label' => 'Benefit'],
            ['value' => 'np', 'label' => 'Qatar Debit Cards'],
            ['value' => 'uaecc', 'label' => 'UAE Debit Cards'],
            ['value' => 's', 'label' => 'Sadad'],
            ['value' => 'ae', 'label' => 'AMEX'],
            ['value' => 'ap', 'label' => 'Apple Pay'],
            ['value' => 'kf', 'label' => 'KFast'],
            ['value' => 'af', 'label' => 'AFS'],
            ['value' => 'stc', 'label' => 'STC Pay'],
            ['value' => 'mz', 'label' => 'Mezza'],
            ['value' => 'oc', 'label' => 'Orange Cash'],
            ['value' => 'on', 'label' => 'Oman Net'],
            ['value' => 'M', 'label' => 'Mpgs'],
            ['value' => 'ccuae', 'label' => 'UAE DEBIT VISA'],
            ['value' => 'vms', 'label' => 'VISA/MASTER Saudi'],
            ['value' => 'vmm', 'label' => 'VISA/MASTER/MADA'],
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {
        return [
            'myfatoorah' => 'MyFatoorah',
            'kn'         => 'KNET',
            'vm'         => 'VISA/MASTER',
            'md'         => 'MADA',
            'b'          => 'Benefit',
            'np'         => 'Qatar Debit Cards',
            'uaecc'      => 'UAE Debit Cards',
            's'          => 'Sadad',
            'ae'         => 'AMEX',
            'ap'         => 'Apple Pay',
            'kf'         => 'KFast',
            'af'         => 'AFS',
            'stc'        => 'STC Pay',
            'mz'         => 'Mezza',
            'oc'         => 'Orange Cash',
            'on'         => 'Oman Net',
            'M'          => 'Mpgs',
            'ccuae'      => 'UAE DEBIT VISA',
            'vms'        => 'VISA/MASTER Saudi',
            'vmm'        => 'VISA/MASTER/MADA',
        ];
    }

}
