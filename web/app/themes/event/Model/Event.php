<?php

namespace Model;

class Event
{
    private string $titre;
    private string $description;
    private string $adresse;
    private string $date;
    private string $visuel;
    private string $billet;
    private bool $place_illimite;
    private int $nombrePlace;

    /**
     * @param string $titre
     * @param string $description
     * @param string $adresse
     * @param string $date
     * @param string $visuel
     * @param string $billet
     * @param bool $place_illimite
     * @param int $nombrePlace
     */
    public function __construct(string $titre, string $description, string $adresse, string $date, string $visuel, string $billet, bool $place_illimite, int $nombrePlace = 0)
    {
        $this->titre = $titre;
        $this->description = $description;
        $this->adresse = $adresse;
        $this->date = $date;
        $this->visuel = $visuel;
        $this->billet = $billet;
        $this->place_illimite = $place_illimite;
        $this->nombrePlace = $nombrePlace;
    }


    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): void
    {
        $this->titre = $titre;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getAdresse(): string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): void
    {
        $this->adresse = $adresse;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function setDate(string $date): void
    {
        $this->date = $date;
    }

    public function getVisuel(): string
    {
        return $this->visuel;
    }

    public function setVisuel(string $visuel): void
    {
        $this->visuel = $visuel;
    }

    public function getBillet(): string
    {
        return $this->billet;
    }

    public function setBillet(string $billet): void
    {
        $this->billet = $billet;
    }

    public function isPlaceIllimite(): bool
    {
        return $this->place_illimite;
    }

    public function setPlaceIllimite(bool $place_illimite): void
    {
        $this->place_illimite = $place_illimite;
    }

    public function getNombrePlace(): int
    {
        return $this->nombrePlace;
    }

    public function setNombrePlace(int $nombrePlace): void
    {
        $this->nombrePlace = $nombrePlace;
    }
}
