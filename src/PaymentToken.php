<?php

require_once 'src/Payment.php';

class PaymentToken extends Payment {


    public function rules() {
        
        return array(
            'auth_token' => array(
                'notEmpty' => array(
                    'message' => 'Invalid auth_token.'
                )
            ),
            'description' => array(
                'notEmpty' => array(
                    'message' => 'Invalid description.'
                ),
                'maxLength' => array(
                    'params' => 1024,
                    'message' => 'Description is too long.'
                )
            ),
            'amount' => array(
                'notEmpty' => array(
                    'message' => 'Invalid amount.'
                ),
                'maxLength' => array(
                    'params' => 12,
                    'message' => 'Amount is too long.'
                ),
                'greaterThan' => array(
                    'params' => 0,
                    'message' => 'Amount must be positive.'
                ),
                'naturalNumber' => array(
                    'message' => 'The amount should be in cents.'
                )
            ),
            'return_url' => array(
                'notEmpty' => array(
                    'message' => 'Invalid return_url.'
                ),
                'maxLength' => array(
                    'params' => 2048,
                    'message' => 'Return URL is too long.'
                ),
                'url' => array(
                    'message' => 'Invalid return_url.'
                )
            ),
            'issuer' => array(
                'notEmpty' => array(
                    'message' => 'Invalid issuer.'
                ),
                'inList' => array(
                    'params' => array('visa','mastercard','diners','discover','elo','amex','jcb','aura'),
                    'message' => 'Unknown issuer.'
                )
            ),
            'payment_type' => array(
                'notEmpty' => array(
                    'message' => 'Invalid payment_type.'
                ),
                'inList' => array(
                    'params' => array('credito_a_vista', 'credito_parcelado_loja', 'debito'),
                    'message' => 'Unknown payment_type.'
                ),
                'paymentTypeIssuer' => array(
                    'message' => 'Invalid issuer for this payment type.'
                )
            ),
            'installments' => array(
                'notEmpty' => array(
                    'message' => 'Invalid installments.'
                ),
                'installmentsPaymentType' => array(
                    'params' => array('debito', 'credito_a_vista'),
                    'message' => 'The payment type allows only 1 installment.'
                ),
                'installmentsMaxValue' => array(
                    'params' => 8,
                    'message' => 'Invalid installments for this payment_type.'
                )
            ),
            'token' => array(
                'notEmpty' => array(
                    'message' => 'Invalid token.'
                ),
                'maxLength' => array(
                    'params' => 100,
                    'message' => 'token is too long.'
                ),
                'tokenPaymentType' => array(
                    'message' => 'Payment type debit not supported process with token'
                )
            )
        );
    }
}