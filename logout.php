<?php
require_once __DIR__ . '/includes/bootstrap.php';
logout_user();
clear_patient_state();
header('Location: login.php');
