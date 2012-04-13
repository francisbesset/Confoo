<?php

namespace Sensio\Bundle\HangmanBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class PlayerType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('email', 'email')
            ->add('rawPassword', 'repeated', array(
                'type' => 'password',
                'first_name' => 'password',
                'second_name' => 'confirmation'
            ))
        ;
    }

    public function getName()
    {
        return 'player';
    }
}
