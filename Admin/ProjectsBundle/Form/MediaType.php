<?php

namespace Visy\Visy\Admin\ProjectsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'label' => 'Název'
            ])
            ->add('description', 'textarea', [
                'label' => 'Popis',
                'required' => false,
            ])
            ->add('enabled', 'checkbox', [
                'label' => 'Aktivní',
                'required' => false,
                'attr' => [
                    'help_text' => 'Pouze aktivní média jsou veřejná.'
                ]
            ])
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Visy\Visy\ProjectsBundle\Entity\Media'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'visy_visy_admin_projectsbundle_media';
    }
}
