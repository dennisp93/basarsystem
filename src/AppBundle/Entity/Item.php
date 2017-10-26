<?php
/**
 * Created by PhpStorm.
 * User: dpa
 * Date: 31.08.16
 * Time: 17:27
 */

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ItemRepository")
 * @ORM\Table(name="item")
 */
class Item
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string")
     * @Assert\NotBlank(message="Bitte geben Sie eine Artikel Beschreibung ein.")
     */
    private $label;

    /**
     * @var integer
     *
     * @ORM\Column(name="count", type="integer")
     * @Assert\NotBlank(message="Bitte geben Sie die Anzahl der Teile ein.")
     */
    private $count;

    /**
     * @var string
     *
     * @ORM\Column(name="size", type="string")
     * @Assert\NotBlank(message="Bitte geben Sie die Kleidergröße an.")
     */
    private $size;

    /**
     * @var string
     *
     * @ORM\Column(name="minPrice", type="string", nullable=true)
     */
    private $minPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="maxPrice", type="string")
     * @Assert\NotBlank(message="Bitte geben Sie den Preis ein.")
     */
    private $maxPrice;

    /**
     * @var Participant
     *
     * @ManyToOne(targetEntity="AppBundle\Entity\Participant", inversedBy="items")
     * @JoinColumn(name="participant_id", referencedColumnName="id")
     */
    private $owner;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return float
     */
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    /**
     * @param float $minPrice
     */
    public function setMinPrice($minPrice)
    {
        $this->minPrice = $minPrice;
    }

    /**
     * @return float
     */
    public function getMaxPrice()
    {
        return $this->maxPrice;
    }

    /**
     * @param float $maxPrice
     */
    public function setMaxPrice($maxPrice)
    {
        $this->maxPrice = $maxPrice;
    }

    /**
     * @return Participant
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param Participant $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }
}