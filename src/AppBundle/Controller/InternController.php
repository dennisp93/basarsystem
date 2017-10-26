<?php
/**
 * Created by PhpStorm.
 * User: dpa
 * Date: 31.08.16
 * Time: 17:23
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Item;
use AppBundle\Entity\Participant;
use AppBundle\Form\EditParticipantType;
use AppBundle\Form\ItemType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Dennis Paulig <dennis.paulig@i22.de>
 * @Route("/intern")
 */
class InternController extends Controller
{
    /**
     * @Route("/completeData", name="completeData")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function completeDataAction(Request $request)
    {
        /** @var Participant $participant */
        $participant = $this->getUser();
        $participant->setPassword("");
        $participantForm = $this->createForm(EditParticipantType::class);
        $participantForm
            ->add('password', PasswordType::class, [
                'label' => 'Neues Passwort*'
            ]);
        $participantForm->setData($participant);

        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            $participant = $participantForm->getData();

            $clearPassword = $participant->getPassword();
            $password = $this->get('security.password_encoder')->encodePassword($participant, $clearPassword);
            $participant->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($participant);
            $em->flush();

            if ($participant->getRole() === 'ROLE_ADMIN') {
                return $this->redirectToRoute('adminOverview');
            }

            return $this->redirectToRoute('overview');
        }

        return $this->render('AppBundle:intern:completeData.html.twig', [
            'participant' => $participant,
            'form' => $participantForm->createView()
        ]);
    }

    /**
     * @Route("/overview", name="overview")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function overviewAction()
    {
        /** @var Participant $user */
        $user = $this->getUser();
        if ($user->isIncomplete()) {
            return $this->redirectToRoute('completeData');
        }

        $items = $this->getDoctrine()->getRepository('AppBundle:Item')->findBy([
            'owner' => $user
        ]);

        return $this->render('AppBundle:intern:overview.html.twig', [
            'items' => $items
        ]);
    }

    /**
     * @Route("/addItem", name="addItem")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addItemAction(Request $request)
    {
        /** @var Participant $user */
        $user = $this->getUser();
        if ($user->isIncomplete()) {
            return $this->redirectToRoute('completeData');
        }

        $itemForm = $this->createForm(ItemType::class);

        $itemForm->handleRequest($request);

        if ($itemForm->isSubmitted() && $itemForm->isValid()) {
            /** @var Item $item */
            $item = $itemForm->getData();
            $item->setOwner($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($item);
            $em->flush();

            return $this->redirectToRoute('overview');
        }

        return $this->render('AppBundle:intern:addItem.html.twig', [
            'form' => $itemForm->createView()
        ]);
    }

    /**
     * @Route("/editItem/{id}", name="editItem")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editItemAction($id, Request $request)
    {
        /** @var Participant $user */
        $user = $this->getUser();
        if ($user->isIncomplete()) {
            return $this->redirectToRoute('completeData');
        }

        $item = $this->getDoctrine()->getRepository('AppBundle:Item')->findOneBy(['id' => $id]);
        $itemForm = $this->createForm(ItemType::class);
        $itemForm->setData($item);

        $itemForm->handleRequest($request);

        if ($itemForm->isSubmitted() && $itemForm->isValid()) {
            $item = $itemForm->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($item);
            $em->flush();

            if ($user->getRole() === 'ROLE_ADMIN') {
                return $this->redirectToRoute('adminOverview');
            }
            return $this->redirectToRoute('overview');
        }

        return $this->render('AppBundle:intern:editItem.html.twig', [
            'item' => $item,
            'form' => $itemForm->createView()
        ]);
    }

    /**
     * @Route("/deleteItem/{id}", name="deleteItem")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteItemAction($id, Request $request)
    {
        /** @var Participant $user */
        $user = $this->getUser();
        if ($user->isIncomplete()) {
            return $this->redirectToRoute('completeData');
        }

        $item = $this->getDoctrine()->getRepository('AppBundle:Item')->findOneBy(['id' => $id]);

        $form = $this->createFormBuilder()
            ->add('delete', SubmitType::class, array('label' => 'Löschen'))
            ->add('cancel', SubmitType::class, array('label' => 'Abbrechen'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($item);
                $em->flush();

                if ($user->getRole() === 'ROLE_ADMIN') {
                    return $this->redirectToRoute('adminOverview');
                }
                return $this->redirectToRoute('overview');
            } else {
                if ($user->getRole() === 'ROLE_ADMIN') {
                    return $this->redirectToRoute('adminOverview');
                }
                return $this->redirectToRoute('overview');
            }
        }

        return $this->render('AppBundle:intern:deleteItem.html.twig', [
            'item' => $item,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/editParticipant", name="editParticipant")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editParticipantAction(Request $request)
    {
        /** @var Participant $user */
        $user = $this->getUser();
        if ($user->isIncomplete()) {
            return $this->redirectToRoute('completeData');
        }

        $participantForm = $this->createForm(EditParticipantType::class);
        $participantForm->setData($user);

        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            $participant = $participantForm->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($participant);
            $em->flush();

            return $this->redirectToRoute('overview');
        }

        return $this->render('AppBundle:intern:editParticipant.html.twig', [
            'participant' => $user,
            'form' => $participantForm->createView()
        ]);
    }

    /**
     * @Route("/leaveBasar", name="leaveBasar")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function leaveBasarAction(Request $request)
    {
        /** @var Participant $user */
        $user = $this->getUser();
        if ($user->isIncomplete()) {
            return $this->redirectToRoute('completeData');
        }

        /** @var Participant $participant */
        $participant = $this->getDoctrine()->getRepository('AppBundle:Participant')->findOneBy(['id' => $user->getId()]);

        $form = $this->createFormBuilder()
            ->add('delete', SubmitType::class, array('label' => 'Löschen'))
            ->add('cancel', SubmitType::class, array('label' => 'Abbrechen'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $items = $this->getDoctrine()->getRepository('AppBundle:Item')->findBy([
                    'owner' => $user
                ]);

                $em = $this->getDoctrine()->getManager();

                foreach ($items as $item) {
                    $em->remove($item);
                }
                $em->flush();

                // Set User inactive to mark as deleted
                $participant->setStatus(Participant::STATUS_WIPEPENDING);
                $participant->setEnabled(false);

                $em->flush();
                return $this->redirectToRoute('logout');
            } else {
                return $this->redirectToRoute('overview');
            }
        }

        return $this->render('AppBundle:intern:leaveBasar.html.twig', [
            'user' => $participant,
            'form' => $form->createView()
        ]);
    }
}