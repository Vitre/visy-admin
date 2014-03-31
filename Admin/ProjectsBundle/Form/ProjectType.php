<?php

namespace Visy\Visy\Admin\ProjectsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProjectType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'Název'
            ))
            ->add('author', 'text', array(
                'label' => 'Autor'
            ))
            ->add('location', 'text', array(
                'label' => 'Lokalita'
            ))
            /*
            ->add('type', 'text', array(
                'label' => 'Název'
            ))
            */
            ->add('description', 'textarea', array(
                'label' => 'Popis'
            ))
            ->add('content', 'ckeditor', array(
                'label'  => 'Obsah',
                'config' => [
                    'height' => '200px',
                    'toolbar' => [
                        ['name' => 'document', 'items' => ['Source', '-', 'Save', 'NewPage', 'DocProps', 'Preview', 'Print', '-', 'Templates']],
                        ['name' => 'clipboard', 'items' => ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']],
                        ['name' => 'editing', 'items' => ['Find', 'Replace', '-', 'SelectAll', '-', 'SpellChecker', 'Scayt']],
                        //['name' => 'forms', 'items' => ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField']],
                        ['name' => 'tools', 'items' => ['Maximize', 'ShowBlocks', '-', 'About']],
                        '/',
                        ['name' => 'basicstyles', 'items' => ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']],
                        ['name' => 'paragraph', 'items' => ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl']],
                        ['name' => 'links', 'items' => ['Link', 'Unlink', 'Anchor']],
                        //['name' => 'insert', 'items' => ['Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak']],
                        //'/',
                        //['name' => 'styles', 'items' => ['Styles', 'Format', 'Font', 'FontSize']],
                        //['name' => 'colors', 'items' => ['TextColor', 'BGColor']],
                    ]
                ]
            ))
            ->add('tags', 'text', [
                'label' => 'Tagy'
            ])
            ->add('voteCount', 'text', [
                'label' => 'Počet hlasů'
            ])
            ->add('enabled', 'checkbox', [
                'label' => 'Aktivní',
                'required' => false,
                'attr' => [
                    'help_text' => 'Pouze aktivní projekty jsou veřejné.'
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
            'data_class' => 'Visy\Visy\ProjectsBundle\Entity\Project'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'visy_visy_admin_projectsbundle_project';
    }
}
