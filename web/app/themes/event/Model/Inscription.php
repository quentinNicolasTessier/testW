<?php

namespace Model;

use WP_Query;

class Inscription
{
    private string $nom;
    private string $prenom;
    private string $email;
    private string $dateNaissance;
    private string $statut;
    private int $eventId;

    /**
     * @param string $nom
     * @param string $prenom
     * @param string $email
     * @param string $dateNaissance
     * @param string $statut
     * @param int $eventId
     */
    public function __construct(string $nom, string $prenom, string $email, string $dateNaissance, string $statut, int $eventId)
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->dateNaissance = $dateNaissance;
        $this->statut = $statut;
        $this->eventId = $eventId;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getDateNaissance(): string
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(string $dateNaissance): void
    {
        $this->dateNaissance = $dateNaissance;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): void
    {
        $this->statut = $statut;
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }

    public function setEventId(int $eventId): void
    {
        $this->eventId = $eventId;
    }
}
