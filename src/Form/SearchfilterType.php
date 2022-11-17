<?php

namespace App\Form;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchfilterType extends AbstractType
{
    private const CHOICES = [
        'today' => 'today',
        'yesterday' => 'yesterday',
        '3 days ago' => '3days',
        '5 days ago' => '5days',
        'this week' => 'week',
        '2 weeks ago' => '2weeks',
        'current month' => 'month',
    ];

    public function __construct(private readonly ProjectRepository $projectRepository)
    {
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
                'choice_label' => function (Project $project) {
                    return sprintf('%s', $project->getName());
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
                'label' => '<i class="fas fa-filter"> Filter</i>',
                'attr' => ['class' => 'btn btn-outline-success'],
                'label_html' => true,
            ])
            ->add('download', SubmitType::class, [
                'label' => '<i class="fas fa-download"> Download</i>',
                'attr' => ['class' => 'btn btn-outline-primary'],
                'label_html' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([

        ]);
    }
}
