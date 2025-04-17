<?php

namespace App\Controller;

use App\Entity\Note;
use App\Entity\User;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/home')]
class NoteController extends AbstractController
{
    private $entityManager;
    private $noteRepository;

    public function __construct(EntityManagerInterface $entityManager, NoteRepository $noteRepository)
    {
        $this->entityManager = $entityManager;
        $this->noteRepository = $noteRepository;
    }

    #[Route('/note/new', name: 'notes_new')]
    public function new(Request $request): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('connexion_page');
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $request->getSession()->remove('user_id');
            return $this->redirectToRoute('connexion_page');
        }

        if ($request->isMethod('POST')) {
            $noteText = $request->request->get('note_text');
            
            if (!empty($noteText)) {
                $note = new Note();
                $note->setUser($user);
                $note->setNoteText($noteText);
                
                $this->entityManager->persist($note);
                $this->entityManager->flush();
                
                $this->addFlash('success', 'Note added successfully!');
                return $this->redirectToRoute('home_page');
            } else {
                $this->addFlash('error', 'Please enter a note text.');
            }
        }

        return $this->render('notes/new.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/note/{id}/edit', name: 'notes_edit')]
    public function edit(Request $request, int $id): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('connexion_page');
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $request->getSession()->remove('user_id');
            return $this->redirectToRoute('connexion_page');
        }

        $note = $this->noteRepository->find($id);
        if (!$note) {
            throw $this->createNotFoundException('Note not found');
        }

        if ($note->getUser()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('You are not allowed to edit this note');
        }

        if ($request->isMethod('POST')) {
            $noteText = $request->request->get('note_text');
            
            if (!empty($noteText)) {
                $note->setNoteText($noteText);
                $this->entityManager->flush();
                
                $this->addFlash('success', 'Note updated successfully!');
                return $this->redirectToRoute('home_page');
            } else {
                $this->addFlash('error', 'Please enter a note text.');
            }
        }

        return $this->render('notes/edit.html.twig', [
            'note' => $note,
            'user' => $user
        ]);
    }

    #[Route('/note/{id}/delete', name: 'notes_delete')]
    public function delete(Request $request, int $id): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('connexion_page');
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $request->getSession()->remove('user_id');
            return $this->redirectToRoute('connexion_page');
        }

        $note = $this->noteRepository->find($id);
        if (!$note) {
            throw $this->createNotFoundException('Note not found');
        }

        if ($note->getUser()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('You are not allowed to delete this note');
        }

        $this->entityManager->remove($note);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Note deleted successfully!');
        return $this->redirectToRoute('home_page');
    }
}