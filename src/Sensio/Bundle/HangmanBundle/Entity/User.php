<?php

namespace Sensio\Bundle\HangmanBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * Sensio\Bundle\HangmanBundle\Entity\User
 *
 * @ORM\Table(name="sl_users")
 * @ORM\Entity(repositoryClass="Sensio\Bundle\HangmanBundle\Entity\UserRepository")
 * @UniqueEntity(fields={ "username" }, message="This username already exists.")
 * @UniqueEntity(fields={ "email" }, message="This email address already exists.")
 */
class User implements UserInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $username
     *
     * @ORM\Column(name="username", type="string", length=15, unique=true)
     * @Assert\NotBlank()
     * @Assert\MinLength(6)
     * @Assert\MaxLength(15)
     * @Assert\Regex(pattern="/^[a-z0-9]+$/i", message="Username only accepts letters and digits.")
     */
    private $username;

    /**
     * @var string $salt
     *
     * @ORM\Column(name="salt", type="string", length=100)
     */
    private $salt;

    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", length=100)
     */
    private $password;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=60, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var boolean $isActive
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=true)
     */
    private $isActive;

    /**
     * @Assert\NotBlank()
     * @Assert\MinLength(6)
     */
    private $rawPassword;

    /**
     * @ORM\OneToMany(targetEntity="GameData", mappedBy="player")
     *
     */
    private $games;

    public function __construct()
    {
        $this->isActive = true;
        $this->salt = sha1(uniqid().rand(0, 99999999).microtime());
        $this->games = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->username;
    }

    public function getGames()
    {
        return $this->games;
    }

    public function setRawPassword($rawPassword)
    {
        $this->rawPassword = $rawPassword;
    }

    public function getRawPassword()
    {
        return $this->rawPassword;
    }

    public function updatePassword(PasswordEncoderInterface $encoder)
    {
        $this->password = $encoder->encodePassword(
            $this->rawPassword,
            $this->salt
        );

        $this->eraseCredentials();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set salt
     *
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    public function getRoles()
    {
        return array('ROLE_PLAYER');
    }

    public function eraseCredentials()
    {
        $this->rawPassword = null;
    }

    public function equals(UserInterface $user)
    {
        return $this->username === $user->getUsername();
    }
}