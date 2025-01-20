<?php

namespace App\Console;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Office;
use Illuminate\Support\Facades\Schema;
use Slim\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Faker\Factory as Faker;

class PopulateDatabaseCommand extends Command
{
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('db:populate');
        $this->setDescription('Populate database');
    }

    protected function execute(InputInterface $input, OutputInterface $output ): int
    {
        $output->writeln('Populate database...');

        /** @var \Illuminate\Database\Capsule\Manager $db */
        $db = $this->app->getContainer()->get('db');
        $faker = Faker::create('fr_FR');

        $db->getConnection()->statement("SET FOREIGN_KEY_CHECKS=0");
        $db->getConnection()->statement("TRUNCATE `employees`");
        $db->getConnection()->statement("TRUNCATE `offices`");
        $db->getConnection()->statement("TRUNCATE `companies`");
        $db->getConnection()->statement("SET FOREIGN_KEY_CHECKS=1");


        $this->generateCompanies($db, $faker, 4);
        $this->generateOffice($db, $faker, 3);
        $this->generateEmployee($db, $faker, 10);

        $db->getConnection()->statement("update companies set head_office_id = 1 where id = 1;");
        $db->getConnection()->statement("update companies set head_office_id = 3 where id = 2;");

        $output->writeln('Database created successfully!');
        return 0;
    }

    protected function generateCompanies($db, $faker, $nbCompanies)
    {   
        $rq = "INSERT INTO `companies` VALUES ";
        for ($i = 0; $i < $nbCompanies; $i++) {
            $companyId = $i + 1;
            $companyName = $faker->company();
            $companyNumber = $faker->e164PhoneNumber();
            $companyEmail = $faker->companyEmail();
            $companyWebsite = $faker->url();

            $statement = "('$companyId', '$companyName', '$companyNumber', '$companyEmail', '$companyWebsite', null, now(), now(), null)";
            echo $rq . $statement . "\n";
            $db->getConnection()->statement($rq . $statement);
        }
    }

    protected function generateOffice($db, $faker, $nbOffices)
    {   
        $rq = "INSERT INTO `offices` VALUES ";
        for ($i = 0; $i < $nbOffices; $i++) {
            $officeId = $i + 1;
            $city = $faker->city();
            $officeName = 'Bureau à ' . $city;
            $officeStreet = $faker->streetAddress();
            $officeZipCode = $faker->postcode();
            $officeCountry = $faker->country();
            $officeEmail = $faker->companyEmail();
            $officePhone = $faker->e164PhoneNumber();
            $companyId = $faker->randomElement($db->getConnection()->select("SELECT id FROM companies"))->id;
            $statement = "($officeId, '$officeName', '$officeStreet', '$city', '$officeZipCode', '$officeCountry', '$officeEmail', '$officePhone', $companyId, now(), now())";
            echo $rq . $statement . "\n";
            $db->getConnection()->statement($rq . $statement);

        }
    }

    protected function generateEmployee($db, $faker, $nbEmployees)
    {   
        $rq = "INSERT INTO `employees` VALUES ";
        for ($i = 0; $i < $nbEmployees; $i++) {
            $employeeId = $i + 1;
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $officeId = $faker->randomElement($db->getConnection()->select("SELECT id FROM offices"))->id;
            $email = $faker->email();
            $phone = $faker->phoneNumber();
            $jobTitle = $faker->jobTitle();

            $statement ="($employeeId, '$firstName', '$lastName', $officeId, '$email', '$phone', '$jobTitle', now(), now())";
            echo $rq . $statement . "\n";
            $db->getConnection()->statement($rq . $statement);
        }
    }
}
