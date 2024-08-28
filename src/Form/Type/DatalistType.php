<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatalistType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['choices']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['choices'] = $options['choices'];
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'datalist';
    }
}