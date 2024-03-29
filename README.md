<div align="center">
    <p>
        <a href="https://github.com/404NotFoundIndonesia/" target="_blank">
            <img src="https://avatars.githubusercontent.com/u/87377917?s=200&v=4" width="200" alt="404NFID Logo">
        </a>
    </p>

 [![GitHub Stars](https://img.shields.io/github/stars/404NotFoundIndonesia/tuhfah-webapp.svg)](https://github.com/404NotFoundIndonesia/tuhfah-webapp/stargazers)
 [![GitHub license](https://img.shields.io/github/license/404NotFoundIndonesia/tuhfah-webapp)](https://github.com/404NotFoundIndonesia/tuhfah-webapp/blob/main/LICENSE)
 
</div>

# Tuhfah Web Application

__Tuhfah__ is a cutting-edge information and management system designed to revolutionize Islamic Education Parks (TPQs) or Islamic Education Centers (TPAs). It offers a seamless and integrated platform for efficient administration and enhanced learning experiences within these institutions.

With __Tuhfah__, administrators can streamline registration processes, manage user roles, and track attendance effortlessly. Teachers benefit from tools to monitor students' learning progress and input educational data seamlessly. Students and guardians gain access to personalized learning insights and updates.

Financial management becomes more transparent and convenient with integrated online payment systems for fees and honorariums. The system also facilitates inventory management and efficient communication through announcements.

__Tuhfah__ is designed to be user-friendly, intuitive, and accessible across devices, ensuring a smooth experience for all stakeholders. It aims to elevate the standards of Islamic education management and empower institutions with modern technology solutions.

## Goals

- Develop a robust information and management system tailored for TPQs/TPAs.
- Improve administrative efficiency, data management, and learning processes within Islamic education institutions.
- Enable multiple TPQs/TPAs to use the system concurrently.
- Provide a seamless and integrated experience for administrators, teachers, and students.

## Features

- Registration and User Management:
    - Digital registration form for new students.
    - Admin capabilities to add, remove, and manage users (admins, teachers, and students).
    - User roles and permissions management.
- Attendance Management:
    - Real-time attendance tracking for students and teachers via mobile and web applications.
    - Automatic integration of attendance data into the management system.
- Learning Progress Monitoring:
    - Teachers can input and monitor students' learning progress.
    - Admin and guardians can track students' educational development.
- Financial Management:
    - Online payment integration for tuition fees and teacher honorariums.
    - Automatic financial record-keeping and manual input options for admins.
- Inventory and Announcements:
    - Inventory management for physical assets.
    - Announcement creation and dissemination by admins, accessible to the public through the official TPQ/TPA website.
- Additional Features:
    - Notification system for attendance reminders, payment deadlines, and learning updates.
    - Integration with online payment gateways for seamless financial transactions.
    - Reporting and data analysis tools for performance evaluation and educational insights.

## Technical Specifications

- __Backend__: PHP 8.3, Laravel 11.0
- __Database__: MySQL Community Server
- __Frontend__: HTML, CSS, JavaScript, Svelte 4.12
- __Notification__: Firebase Cloud Messaging

## Get Started

### Get the Source Code
Of course, you need to put this code on your computer first. There are two ways to do this: by __downloading the project zip file__ or __by using Git (recommended)__.

1. **Download the Project Zip**

    You can click on [this link](https://github.com/404NotFoundIndonesia/tuhfah-webapp/archive/refs/heads/main.zip) to download the zip file of this project.

2. **Git Clone**

    Make sure that you have installed git. Open the directory where you want to place the source code in the terminal. Then, run the following command:
    ```shell
    git clone git@github.com:404NotFoundIndonesia/tuhfah-webapp.git
    ```

### Install Dependencies

There are two types of dependencies that we need to install for this project: one for the backend and one for the frontend. For the backend, we use Composer for dependency management, while for the frontend, we use npm to install dependencies.

And __make sure this project is open in your command line interface__. To confirm your current active directory in the terminal, use the following command: 
```shell
pwd
```

To install backend dependencies, use the following command:
```shell
composer install
```

To install frontend dependencies, use the following command:
```shell
npm install
```

### How to Run

You need to open two command line instances to run this project. Each is used for the backend and frontend parts. Also, make sure the active directory in each command line is within this project.

To run the backend server, use the following command:
```shell
php artisan serve
```

To run the frontend server, use the following command:
```shell
npm run dev
```

Open `http://localhost:8000` in your browser to access Math Rizz. When you want to access __Tuhfah__ in a web browser, make sure you **do not close or stop both of those processes**.

## Reference
[Effendi, M. Iqbal dan Nafila Fayruz. 2021. Sistem Informasi dan Manajemen Taman Pendidikan Alquran Imam Syafi'i Banjarmasin Berbasis Web dan Aplikasi Android. _Tugas Akhir Diploma 3_. Banjarmasin: Politeknik Negeri Banjarmasin.](https://drive.google.com/file/d/1IcnC0AzTEy1HQBOAmqEvJy7vNhtv4uMu/view?usp=sharing)

## License

__Tuhfah Web Application__ is open-sourced software licensed under the [MIT license](https://github.com/404NotFoundIndonesia/tuhfah-webapp?tab=MIT-1-ov-file).
