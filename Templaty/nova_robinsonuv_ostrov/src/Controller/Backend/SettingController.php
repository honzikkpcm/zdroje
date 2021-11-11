<?php

namespace App\Controller\Backend;

use App\Service\Setting;
use App\Service\SmartEmailing;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SettingController
 * @package App\Controller\Backend
 */
class SettingController extends BackendController
{
    /**
     * @Route("/setting", name="setting")
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('Backend/view/setting.html.twig', [
            'title' => 'Setting',
        ]);
    }

    /**
     * @Route("/setting/edit/smartemailing", name="setting-smartemailing")
     * @param Request $request
     * @param Setting $setting
     * @param SmartEmailing $sm
     * @return Response
     */
    public function editSmartEmailing(Request $request, Setting $setting, SmartEmailing $sm): Response
    {
        $list = $sm->getContactListsList();

        $form = $this->getFormSmartEmailing([
            'smartemailing_list_registration' => $setting->get('smartemailing_list_registration'),
            'smartemailing_list_not_closed_c' => $setting->get('smartemailing_list_not_closed_c'),
            'smartemailing_list_banned' => $setting->get('smartemailing_list_banned'),
        ], $list);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $values = $form->getData();

            $setting->set('smartemailing_list_registration', $values['smartemailing_list_registration']);
            $setting->set('smartemailing_list_not_closed_c', $values['smartemailing_list_not_closed_c']);
            $setting->set('smartemailing_list_banned', $values['smartemailing_list_banned']);

            $this->addFlash('success', 'The items has been updated.');
            return $this->json([
                'redirect' => $this->generateUrl('setting'),
            ]);
        }

        return $this->render('Backend/view/modal.html.twig', [
            'form' => $form->createView(),
            'h1' => 'Edit Smart Emailing',
        ]);
    }

    // private methods -------------------------------------------------------------------------------------------------

    /**
     * @param array $default
     * @param array $list
     * @return FormInterface
     */
    private function getFormSmartEmailing(array $default, array $list): FormInterface
    {
        $list = array_flip($list);

        $builder = $this->createFormBuilder($default)
            ->add('smartemailing_list_registration', ChoiceType::class, [
                'choices' => $list,
                'label' => 'Registration list',
            ])
            ->add('smartemailing_list_not_closed_c', ChoiceType::class, [
                'choices' => $list,
                'label' => 'Not closed challenged list',
            ])
            ->add('smartemailing_list_banned', ChoiceType::class, [
                'choices' => $list,
                'label' => 'Banned list',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
            ])
            ->setAction($this->generateUrl('setting-smartemailing'));

        return $builder->getForm();
    }

}
