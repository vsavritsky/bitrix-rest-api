<?php

namespace App\Model;

class UserAddModel
{
    public string|null $name;
    public string|null $lastName;
    public string|null $middleName;

    public string|null $workCompany;
    public string|null $workPosition;
    public string|null $gender;
    public string|null $password;
    public string|null $birthday;

    public string|null $email;

    public string|null $phone;

    public string|null $picture;

    public function __construct($data)
    {
        $this->name = $data['name'];
        $this->lastName = $data['lastName'];
        $this->middleName = $data['middleName'];
        $this->workCompany = $data['workCompany'];
        $this->workPosition = $data['workPosition'];
        $this->password = $data['password'];
        $this->birthday = $data['birthday'];
        $this->gender = $data['gender'];
        $this->email = $data['email'];
        $this->phone = $data['phone'];
        $this->picture = $data['picture'];
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getMiddleName(): mixed
    {
        return $this->middleName;
    }

    /**
     * @param mixed $middleName
     */
    public function setMiddleName(mixed $middleName): void
    {
        $this->middleName = $middleName;
    }

    /**
     * @return mixed
     */
    public function getGender(): mixed
    {
        return $this->gender;
    }

    /**
     * @param mixed $gender
     */
    public function setGender(mixed $gender): void
    {
        $this->gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return string|null
     */
    public function getBirthday(): ?string
    {
        return $this->birthday;
    }

    /**
     * @param string|null $birthday
     */
    public function setBirthday(?string $birthday): void
    {
        $this->birthday = $birthday;
    }

    /**
     * @return mixed
     */
    public function getWorkCompany(): mixed
    {
        return $this->workCompany;
    }

    /**
     * @param mixed $workCompany
     */
    public function setWorkCompany(mixed $workCompany): void
    {
        $this->workCompany = $workCompany;
    }

    /**
     * @return string|null
     */
    public function getWorkPosition(): ?string
    {
        return $this->workPosition;
    }

    /**
     * @param string|null $workPosition
     */
    public function setWorkPosition(?string $workPosition): void
    {
        $this->workPosition = $workPosition;
    }
}
