<?php
/**
 * Created by PhpStorm.
 * User: dpa
 * Date: 31.08.16
 * Time: 17:34
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Item;
use AppBundle\Entity\Participant;
use AppBundle\Form\EditParticipantType;
use AppBundle\Form\ParticipantType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use XLSXWriter;

/**
 * @author Dennis Paulig <dennis.paulig@i22.de>
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/overview", name="adminOverview")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function overviewAction(Request $request)
    {
        /** @var Participant $user */
        $user = $this->getUser();
        if ($user->isIncomplete()) {
            return $this->redirectToRoute('completeData');
        }

        $wipePendingUsers = $this->getDoctrine()->getRepository('AppBundle:Participant')->findBy([
            'status' => 'wipepending'
        ]);

        if (count($wipePendingUsers) > 0) {
            return $this->redirectToRoute('adminWipeUsers');
        }

        $items = $this->getDoctrine()->getRepository('AppBundle:Item')->findAll();

        return $this->render('AppBundle:admin:overview.html.twig', [
            'items' => $items
        ]);
    }

    /**
     * @Route("/participantOverview", name="adminParticipantOverview")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function participantOverviewAction(Request $request)
    {
        /** @var Participant $user */
        $user = $this->getUser();
        if ($user->isIncomplete()) {
            return $this->redirectToRoute('completeData');
        }

        $participants = $this->getDoctrine()->getRepository('AppBundle:Participant')->findAll();

        return $this->render('AppBundle:admin:participantOverview.html.twig', [
            'participants' => $participants
        ]);
    }

    /**
     * @Route("/addParticipant", name="adminAddParticipant")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addParticipantAction(Request $request)
    {
        /** @var Participant $user */
        $user = $this->getUser();
        if ($user->isIncomplete()) {
            return $this->redirectToRoute('completeData');
        }

        $participantForm = $this->createForm(ParticipantType::class, null, [
            'validation_groups' => 'registration'
        ]);

        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            /** @var Participant $participant */
            $participant = $participantForm->getData();

            $participant->setStatus(Participant::STATUS_ACTIVE);
            $participant->setEnabled(true);

            $clearPassword = substr(uniqid(), -10);
            $password = $this->get('security.password_encoder')->encodePassword($participant, $clearPassword);
            $participant->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($participant);
            $em->flush();

            $this->get('MailProvider')->sendInvitationMail($participant, $clearPassword);

            return $this->redirectToRoute('adminParticipantOverview');
        }

        return $this->render('AppBundle:admin:addParticipant.html.twig', [
            'form' => $participantForm->createView()
        ]);
    }

    /**
     * @Route("/editParticipant/{id}", name="adminEditParticipant")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editParticipantAction($id, Request $request)
    {
        /** @var Participant $user */
        $user = $this->getUser();
        if ($user->isIncomplete()) {
            return $this->redirectToRoute('completeData');
        }

        $participant = $this->getDoctrine()->getRepository('AppBundle:Participant')->findOneBy(['id' => $id]);
        $participantForm = $this->createForm(EditParticipantType::class);
        $participantForm->setData($participant);

        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            $participant = $participantForm->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($participant);
            $em->flush();

            return $this->redirectToRoute('adminParticipantOverview');
        }

        return $this->render('AppBundle:admin:editParticipant.html.twig', [
            'participant' => $participant,
            'form' => $participantForm->createView()
        ]);
    }

    /**
     * @Route("/deleteParticipant/{id}", name="adminDeleteParticipant")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteParticipantAction($id, Request $request)
    {
        /** @var Participant $user */
        $user = $this->getUser();
        if ($user->isIncomplete()) {
            return $this->redirectToRoute('completeData');
        }

        $participant = $this->getDoctrine()->getRepository('AppBundle:Participant')->findOneBy(['id' => $id]);

        $form = $this->createFormBuilder()
            ->add('delete', SubmitType::class, array('label' => 'Löschen'))
            ->add('cancel', SubmitType::class, array('label' => 'Abbrechen'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $em = $this->getDoctrine()->getManager();

                $items = $this->getDoctrine()->getRepository('AppBundle:Item')->findBy([
                    'owner' => $participant
                ]);

                foreach ($items as $item) {
                    $em->remove($item);
                }
                $em->flush();

                $em->remove($participant);
                $em->flush();
                return $this->redirectToRoute('adminParticipantOverview');
            } else {
                return $this->redirectToRoute('adminParticipantOverview');
            }
        }

        return $this->render('AppBundle:admin:deleteParticipant.html.twig', [
            'participant' => $participant,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/wipeUsers", name="adminWipeUsers")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function wipeUsersAction(Request $request)
    {
        $wipePendingUsers = $this->getDoctrine()->getRepository('AppBundle:Participant')->findBy([
            'status' => 'wipepending'
        ]);

        $em = $this->getDoctrine()->getManager();

        foreach ($wipePendingUsers as $user) {
            $em->remove($user);
        }
        $em->flush();

        return $this->render('AppBundle:admin:wipeUsers.html.twig', [
            'wipeUsers' => $wipePendingUsers
        ]);
    }

    /**
     * @Route("/indexCards", name="adminIndexCards")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexCardsAction()
    {
        $participants = $this->getDoctrine()->getRepository('AppBundle:Participant')->findBy([], ['lastname' => 'ASC']);

        return $this->render('AppBundle:admin:indexcards.html.twig', [
            'participants' => $participants
        ]);
    }

    /**
     * @Route("/export", name="adminExcelOverview")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportAction()
    {
        return $this->render('AppBundle:admin:exportOverview.html.twig', []);
    }

    /**
     * @Route("/export/preisliste", name="adminExcelPreisliste")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportPreislisteAction()
    {
        $participants = $this->getDoctrine()->getRepository('AppBundle:Participant')->findBy([], ['lastname' => 'ASC']);

        $header = [
            'Nr.'=>'string',
            'Artikel'=>'string',
            'Preis'=>'euro',
            'Größe'=>'string'
        ];

        $data = [];

        $participantCount = 0;

        foreach ($participants as $participant) {
            $participantCount = $participantCount + 1;

            $itemCount = 0;

            /** @var Item $item */
            foreach($participant->getItems() as $item) {
                $itemCount = $itemCount + 1;

                $data[] = [$participantCount . '-' . $itemCount, $item->getLabel(), $item->getMaxPrice(), $item->getSize()];
            }

        }

        $writer = new XLSXWriter();
        $writer->setAuthor('KoBa-Team');
        $writer->writeSheet($data,'Sheet1', $header);

        $timestamp = new \DateTime();

        return new Response($writer->writeToString(), 200, [
            'content-type' => 'text/vnd.ms-excel; charset=utf-8',
            'content-disposition' => 'attachment; filename=preisliste_' . $timestamp->format('Y-m-y_H-i-s') . '.xlsx',
            'content-transfer-encoding' => 'binary'
        ]);
    }

    /**
     * @Route("/export/haftungsliste", name="adminExcelHaftungsliste")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportHaftungslisteAction()
    {
        $participants = $this->getDoctrine()->getRepository('AppBundle:Participant')->findBy([], ['lastname' => 'ASC']);

        $header = [
            'Name'=>'string',
            'abgegeben'=>'string',
            '1 Euro'=>'string',
            'Umsatz'=>'string',
            'Auszahlungsbetrag'=>'string',
            'Betrag erhalten / Ware entgegengenommen'=>'string'
        ];

        $data = [];

        foreach ($participants as $participant) {
            $data[] = [$participant->getLastname() . ', ' . $participant->getFirstname(), '', '', '', '', ''];
        }

        $writer = new XLSXWriter();
        $writer->setAuthor('KoBa-Team');
        $writer->writeSheet($data,'Sheet1', $header);

        $timestamp = new \DateTime();

        return new Response($writer->writeToString(), 200, [
            'content-type' => 'text/vnd.ms-excel; charset=utf-8',
            'content-disposition' => 'attachment; filename=anbieterliste-mit-haftungsauschluss_' . $timestamp->format('Y-m-y_H-i-s') . '.xlsx',
            'content-transfer-encoding' => 'binary'
        ]);
    }

    /**
     * @Route("/export/anbieterliste", name="adminExcelAnbieterliste")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportAnbieterlisteAction()
    {
        $participants = $this->getDoctrine()->getRepository('AppBundle:Participant')->findBy([], ['lastname' => 'ASC']);

        $header = [
            'Nr.'=>'string',
            'Name'=>'string',
            'Telefon'=>'string',
            'Anzahl Teile'=>'string',
            'Artikel'=>'string',
            'Größe'=>'string',
            'Preis'=>'string',
            'VB'=>'string',
        ];

        $data = [];

        $participantCount = 0;

        foreach ($participants as $participant) {
            $participantCount = $participantCount + 1;

            $itemCount = 0;

            /** @var Item $item */
            foreach($participant->getItems() as $item) {
                $itemCount = $itemCount + 1;

                $data[] = [
                    $participantCount . '-' . $itemCount,
                    $participant->getLastname() . ', ' . $participant->getFirstname(),
                    $participant->getMobilenumber(),
                    $item->getCount(),
                    $item->getLabel(),
                    $item->getSize(),
                    $item->getMaxPrice(),
                    $item->getMinPrice()
                ];

                if (count($data) % 15 == 0) {
                    $data[] = [
                        'Nr.',
                        'Name',
                        'Telefon',
                        'Anzahl Teile',
                        'Artikel',
                        'Größe',
                        'Preis',
                        'VB',
                    ];
                }
            }

        }

        $writer = new XLSXWriter();
        $writer->setAuthor('KoBa-Team');
        $writer->writeSheet($data,'Sheet1', $header);

        $timestamp = new \DateTime();

        return new Response($writer->writeToString(), 200, [
            'content-type' => 'text/vnd.ms-excel; charset=utf-8',
            'content-disposition' => 'attachment; filename=anbieterliste-komplett_' . $timestamp->format('Y-m-y_H-i-s') . '.xlsx',
            'content-transfer-encoding' => 'binary'
        ]);
    }

    /**
     * @Route("/export/kassenliste", name="adminExcelKassenliste")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportKassenlisteAction()
    {
        $participants = $this->getDoctrine()->getRepository('AppBundle:Participant')->findBy([], ['lastname' => 'ASC']);

        $header = [
            'Nr.'=>'string',
            'Name'=>'string',
            'Artikel'=>'string',
            'Preis'=>'euro',
            'bez.'=>'euro',
            'Summe'=>'euro',
            'Ausz.'=>'euro',
        ];

        $data = [];

        $participantCount = 0;
        $rowCount = 1;

        foreach ($participants as $participant) {
            $participantCount = $participantCount + 1;

            $itemCount = 0;

            /** @var Item $item */
            foreach($participant->getItems() as $item) {
                $itemCount = $itemCount + 1;
                $rowCount = $rowCount + 1;

                if ($itemCount < count($participant->getItems())) {
                    $data[] = [
                        $participantCount . '-' . $itemCount,
                        $participant->getLastname() . ', ' . $participant->getFirstname(),
                        $item->getLabel(),
                        $item->getMaxPrice(),
                        '',
                        '',
                        ''
                    ];
                } else {
                    $sum = [];
                    for ($i = $rowCount - $itemCount + 1; $i <= $rowCount; $i++) {
                        $sum[] = 'E' . $i;
                    }
                    $sum = '=(' . implode("+", $sum) . ')';

                    $data[] = [
                        $participantCount . '-' . $itemCount,
                        $participant->getLastname() . ', ' . $participant->getFirstname(),
                        $item->getLabel(),
                        $item->getMaxPrice(),
                        '',
                        $sum,
                        '=F' . $rowCount . '-0.1*F' . $rowCount . ''
                    ];
                }
            }
        }

        $data[] = ['', 'SUMME', '', $this->excelSumme('D', 2, $rowCount), $this->excelSumme('E', 2, $rowCount), $this->excelSumme('F', 2, $rowCount), $this->excelSumme('G', 2, $rowCount),];
        $data[] = ['', 'ERLÖS', '', '=D' . ($rowCount+1) . '*0.1', '', '=F' . ($rowCount+1) . '*0.1', '=F' . ($rowCount+1) . '-G' . ($rowCount+1),];

        $writer = new XLSXWriter();
        $writer->setAuthor('KoBa-Team');
        $writer->writeSheet($data,'Sheet1', $header);

        $timestamp = new \DateTime();

        return new Response($writer->writeToString(), 200, [
            'content-type' => 'text/vnd.ms-excel; charset=utf-8',
            'content-disposition' => 'attachment; filename=kassenliste_' . $timestamp->format('Y-m-y_H-i-s') . '.xlsx',
            'content-transfer-encoding' => 'binary'
        ]);
    }

    private function excelSumme($field, $start, $end)
    {
        $sum = [];
        for ($i = $start; $i <= $end; $i++) {
            $sum[] = $field . $i;
        }
        return '=(' . implode("+", $sum) . ')';
    }
}