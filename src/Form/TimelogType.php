<?php

namespace App\Form;

use App\Entity\Timelog;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\DataTransformer\ChoicesToValuesTransformer;

class TimelogType extends AbstractType
{
    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start', DateTimeType::class, [
                'widget' => 'single_text',
                // adds a class that can be selected in JavaScript
                'attr' => ['class' => 'js-datepicker'],
                'required' => true

            ])
            ->add('end', DateTimeType::class, [
                'widget' => 'single_text',
                // adds a class that can be selected in JavaScript
                'attr' => ['class' => 'js-datepicker'],
                'required' => true
            ])
            ->add('comment')
            ->add('project', EntityType::class, [
                'label' => 'Project',
                'class' => Project::class,
                'choices' => $this->projectRepository->findAllProjectsAlphabetical(),
                'choice_label' => function(Project $project) {
                    return sprintf('(%d) %s', $project->getId(), $project->getName());
                },
                'placeholder' => 'Please select a project'
            ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Timelog::class,
        ]);
    }
}
