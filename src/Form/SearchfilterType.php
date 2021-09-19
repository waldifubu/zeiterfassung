<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\Timelog;
use App\Repository\ProjectRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SearchfilterType extends AbstractType
{
    private const CHOICES = [
        'today' => 'today',
        'yesterday' => 'yesterday',
        '3 days ago' => '3days',
        '5 days ago' => '5days',
        'this week' => 'week',
        '2 weeks' => '2weeks',
        'month' => 'month',
    ];
    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('datePreselect', ChoiceType::class, [
                'placeholder' => 'Please choose a date range',
                'attr' => ['class' => 'form-control'],
                'choices' => self::CHOICES,
                'required' => false,
            ])
            ->
            add('project', EntityType::class, [
                'class' => Project::class,
                'choices' => $this->projectRepository->findAllProjectsAlphabetical(),
                'choice_label' => function (Project $user) {
                    return sprintf('%s', $user->getName());
                },
                'placeholder' => 'Please choose a project',
                'required' => false,
            ])
            ->add('dateGiven', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('hours', NumberType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filter'
            ])
            ->add('download', SubmitType::class, [
                'label' => 'Download'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([

                               ]);
    }
}
