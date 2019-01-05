-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 05, 2019 at 03:30 PM
-- Server version: 10.1.35-MariaDB
-- PHP Version: 7.2.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quickbooks`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `_account_type_db`
--

CREATE TABLE `_account_type_db` (
  `account_id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `_account_type_db`
--

INSERT INTO `_account_type_db` (`account_id`, `type`) VALUES
(7, 'Advertising'),
(10, 'Subscriptions'),
(11, 'Insurance'),
(15, 'Office Expenses'),
(17, 'Rent'),
(40, 'Depreciation '),
(80, 'Cost of Goods Sold'),
(93, 'Bank Fees'),
(94, 'Cleaning'),
(95, 'Consulting and Accounting'),
(96, 'Entertainment'),
(97, 'Freight and Courier'),
(98, 'General Expenses'),
(99, 'Income Tax Expense'),
(100, 'Interest Expense'),
(101, 'Legal Expenses'),
(102, 'Light, Power, Heating'),
(103, 'Motor Vehicle Expenses'),
(104, 'Printing & Stationary'),
(105, 'Repairs and Maintenance'),
(106, 'Superannuantion'),
(107, 'Telephone & Internet'),
(108, 'Travel - International'),
(109, 'Travel National'),
(110, 'Wages and Salaries');

-- --------------------------------------------------------

--
-- Table structure for table `_project_db`
--

CREATE TABLE `_project_db` (
  `project_id` int(11) NOT NULL,
  `project_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `_project_db`
--

INSERT INTO `_project_db` (`project_id`, `project_name`) VALUES
(1, 'Riverdale'),
(2, 'Stranger Things'),
(3, 'A Series of Unfortunate Events'),
(4, 'Glee');

-- --------------------------------------------------------

--
-- Table structure for table `_relationship_db_customers`
--

CREATE TABLE `_relationship_db_customers` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL DEFAULT '0',
  `entity_type` varchar(100) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_lname` varchar(200) DEFAULT NULL,
  `customer_address` varchar(200) DEFAULT NULL,
  `license` varchar(100) DEFAULT NULL,
  `customer_abn` varchar(100) DEFAULT NULL,
  `representative_name` varchar(100) DEFAULT NULL,
  `representative_lname` varchar(100) DEFAULT NULL,
  `representative_position` varchar(100) DEFAULT NULL,
  `representative_email` varchar(100) DEFAULT NULL,
  `representative_mobile` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(100) DEFAULT NULL,
  `customer_mobile` varchar(100) DEFAULT NULL,
  `customer_fax` varchar(100) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `modified_in` varchar(100) DEFAULT NULL,
  `xero_uid` varchar(100) DEFAULT NULL,
  `quickbooks_uid` varchar(100) DEFAULT NULL,
  `myob_uid` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `xero_status` varchar(50) DEFAULT NULL,
  `source` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `_relationship_db_customers`
--

INSERT INTO `_relationship_db_customers` (`id`, `client_id`, `entity_type`, `customer_name`, `customer_lname`, `customer_address`, `license`, `customer_abn`, `representative_name`, `representative_lname`, `representative_position`, `representative_email`, `representative_mobile`, `customer_phone`, `customer_mobile`, `customer_fax`, `customer_email`, `date_modified`, `modified_by`, `modified_in`, `xero_uid`, `quickbooks_uid`, `myob_uid`, `status`, `xero_status`, `source`) VALUES
(18, 0, NULL, '123 Company', NULL, '123 Street Manila City', NULL, NULL, 'Nino', 'Escueta', NULL, 'ndescueta@gmail.com', NULL, '999-99-99', '09123456789', '09123456789', 'ndescueta@gmail.com', '2018-12-18 02:53:56', 0, NULL, NULL, '17', NULL, NULL, NULL, NULL),
(19, 0, NULL, 'QWERTY Compan', NULL, 'Sample Street, ,, , ', NULL, NULL, 'sd', 'Romero', NULL, '', NULL, '999-99-99', '09123456789', '999-99-99', '', '2018-12-18 02:53:18', 0, NULL, NULL, '6', NULL, NULL, NULL, NULL),
(20, 0, NULL, 'Leki Company 123567', NULL, 'asd Street Manila Philippines', NULL, NULL, 'Leki', 'Romero', NULL, 'leki@gmail.com', NULL, '999-99-99', '0912345689', '999-99-99', 'leki@gmail.com', '2018-12-20 03:44:27', 0, NULL, NULL, '69', NULL, NULL, NULL, NULL),
(21, 0, NULL, 'ABC Company with a display name', NULL, '125 Main Street, Mountain View, USA', NULL, NULL, 'Mel', 'Getutua', NULL, '', NULL, '(555) 555-5555', '', '', '', '2018-12-20 03:12:58', 0, NULL, NULL, '64', NULL, NULL, NULL, NULL),
(22, 0, NULL, 'Geeta Kalapatapu', NULL, '1987 Main St., Middlefield, ', NULL, NULL, 'Geeta', 'Kalapatapu', NULL, '', NULL, '(650) 555-0022', '', '', '', '2018-12-20 05:53:20', 0, NULL, NULL, '10', NULL, NULL, NULL, NULL),
(23, 0, NULL, 'Kempani 123', NULL, '123 Street ', NULL, NULL, 'Lhexy', 'Lekkay', NULL, 'leki@gmail.com', '', '999-99-99', '09123456789', '09123456789', 'leki@gmail.com', '2018-12-21 04:39:05', 0, NULL, NULL, '71', NULL, NULL, NULL, NULL),
(24, 0, NULL, 'Freeman Sporting Goods', NULL, '370 Easy St., Middlefield, ', NULL, NULL, 'Kirby', 'Freeman', NULL, '', NULL, '(650) 555-0987', '(973) 555-8849', '(520) 555-7894', '', '2018-12-21 08:59:11', 0, NULL, NULL, '7', NULL, NULL, NULL, NULL),
(25, 0, NULL, '0969 Ocean View Road', NULL, '370 Easy St., Middlefield, ', NULL, NULL, 'Sasha', 'Tillou', NULL, '', NULL, '(415) 555-9933', '(973) 555-8849', '(520) 555-7894', '', '2018-12-21 08:59:11', 0, NULL, NULL, '8', NULL, NULL, NULL, NULL),
(26, 0, NULL, 'Jeff', NULL, '12 Willow Rd., Menlo Park, ', NULL, NULL, 'Jeff', 'Chin', NULL, '', NULL, '(650) 555-8989', '', '', '', '2018-12-21 09:00:00', 0, NULL, NULL, '12', NULL, NULL, NULL, NULL),
(27, 0, NULL, 'John Melton', NULL, '85 Pine St., Menlo Park, ', NULL, NULL, 'John', 'Melton', NULL, '', NULL, '(650) 555-5879', '', '', '', '2018-12-21 09:00:00', 0, NULL, NULL, '13', NULL, NULL, NULL, NULL),
(28, 0, NULL, 'ABC Infrastractures.', NULL, 'North South East West St., , ', NULL, NULL, 'Steve', 'Stevenson', NULL, '', NULL, '(650) 555-8989', '(650) 555-8989', '(650) 555-8989', '', '2018-12-21 09:01:15', 0, NULL, NULL, '68', NULL, NULL, NULL, NULL),
(29, 0, NULL, 'Mark Cho', NULL, '36 Willow Rd, Menlo Park, ', NULL, NULL, 'Mark', 'Cho', NULL, '', NULL, '(650) 554-1479', '', '', '', '2018-12-21 04:08:55', 0, NULL, NULL, '17', NULL, NULL, NULL, NULL),
(30, 0, NULL, 'Mark Cho', NULL, '36 Willow Rd, Menlo Park, ', NULL, NULL, 'Mark', 'Cho', NULL, '', NULL, '(650) 554-1479', '', '', '', '2018-12-21 04:09:44', 0, NULL, NULL, '17', NULL, NULL, NULL, NULL),
(31, 0, NULL, 'Gevelber Photography', NULL, '1045 Main St., Half Moon Bay, ', NULL, NULL, 'Lisa', 'Gevelber', NULL, '', NULL, '(415) 222-4345', '', '', '', '2018-12-21 04:11:30', 0, NULL, NULL, '11', NULL, NULL, NULL, NULL),
(32, 0, NULL, 'Bill', NULL, '12 Ocean Dr., Half Moon Bay, ', NULL, NULL, 'Bill', 'Lucchini', NULL, '', NULL, '(415) 444-6538', '', '', '', '2018-12-21 04:13:10', 0, NULL, NULL, '2', NULL, NULL, NULL, NULL),
(33, 0, NULL, 'Diego Rodriguez', NULL, '321 Channing, Palo Alto, ', NULL, NULL, 'Diego', 'Rodriguez', NULL, '', NULL, '(650) 555-4477', '', '', '', '2018-12-21 04:13:10', 0, NULL, NULL, '4', NULL, NULL, NULL, NULL),
(34, 0, NULL, '55 Twin Lane', NULL, '370 Easy St., Middlefield, ', NULL, NULL, 'Amelia', '', NULL, '', NULL, '(650) 555-0987', '(973) 555-8849', '(520) 555-7894', '', '2018-12-21 04:13:10', 0, NULL, NULL, '9', NULL, NULL, NULL, NULL),
(35, 0, NULL, 'Dukes Basketball Camp', NULL, '25 Court St., Tucson, ', NULL, NULL, 'Peter', 'Dukes', NULL, '', NULL, '(520) 420-5638', '', '', '', '2018-12-21 04:13:10', 0, NULL, NULL, '5', NULL, NULL, NULL, NULL),
(36, 0, NULL, 'Kate Whelan', NULL, '45 First St., Menlo Park, USA', NULL, NULL, 'Kate', 'Whelan', NULL, '', NULL, '(650) 554-8822', '', '', '', '2018-12-21 04:13:10', 0, NULL, NULL, '14', NULL, NULL, NULL, NULL),
(37, 0, NULL, 'Kempani Company', NULL, '123 Main Street, Mountain View, USA', NULL, NULL, 'Henlo', 'Kingg', NULL, '', NULL, '(555) 555-5555', '', '', '', '2018-12-21 04:13:10', 0, NULL, NULL, '60', NULL, NULL, NULL, NULL),
(38, 0, NULL, 'King', NULL, '125 Main Street, Mountain View, USA', NULL, NULL, 'Jamess', 'Kingg', NULL, '', NULL, '(555) 555-5555', '', '', '', '2018-12-21 04:13:10', 0, NULL, NULL, '63', NULL, NULL, NULL, NULL),
(39, 0, NULL, 'Cool Cars', NULL, '65 Ocean Dr., Half Moon Bay, ', NULL, NULL, 'Grace', 'Pariente', NULL, '', NULL, '(415) 555-9933', '', '', '', '2018-12-21 04:13:10', 0, NULL, NULL, '3', NULL, NULL, NULL, NULL),
(40, 0, NULL, 'Amy', NULL, '4581 Finch St., Bayshore, ', NULL, NULL, 'Amy', 'Lauterbach', NULL, '', NULL, '(650) 555-3311', '', '', '', '2018-12-21 04:13:10', 0, NULL, NULL, '1', NULL, NULL, NULL, NULL),
(43, 0, NULL, '', NULL, ', , ', NULL, NULL, '', '', NULL, '', NULL, '', '', '', '', '2018-12-27 00:48:10', 0, NULL, NULL, '137', NULL, NULL, NULL, NULL),
(44, 0, NULL, '', NULL, ', , ', NULL, NULL, '', '', NULL, '', NULL, '', '', '', '', '2018-12-27 01:06:11', 0, NULL, NULL, '137', NULL, NULL, NULL, NULL),
(45, 0, NULL, '', NULL, ', , ', NULL, NULL, '', '', NULL, '', NULL, '', '', '', '', '2018-12-27 01:06:49', 0, NULL, NULL, '137', NULL, NULL, NULL, NULL),
(46, 0, NULL, '', NULL, ', , ', NULL, NULL, '', '', NULL, '', NULL, '', '', '', '', '2018-12-27 01:07:22', 0, NULL, NULL, '137', NULL, NULL, NULL, NULL),
(47, 0, NULL, '', NULL, '', NULL, NULL, '', '', NULL, '', NULL, '', '', '', '', '2018-12-27 01:21:13', 0, NULL, NULL, '', NULL, NULL, NULL, NULL),
(48, 0, NULL, '', NULL, '', NULL, NULL, '', '', NULL, '', NULL, '', '', '', '', '2018-12-27 01:21:52', 0, NULL, NULL, '', NULL, NULL, NULL, NULL),
(49, 0, NULL, '', NULL, '', NULL, NULL, '', '', NULL, '', NULL, '', '', '', '', '2018-12-27 01:22:29', 0, NULL, NULL, '', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `_relationship_db_employee`
--

CREATE TABLE `_relationship_db_employee` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL DEFAULT '0',
  `employee_name` varchar(50) DEFAULT NULL,
  `employee_lastname` varchar(50) DEFAULT NULL,
  `employee_number` varchar(50) DEFAULT NULL,
  `employee_email` varchar(50) DEFAULT NULL,
  `employee_phone` varchar(50) DEFAULT NULL,
  `employee_fax` varchar(50) DEFAULT NULL,
  `employee_mobile` varchar(50) DEFAULT NULL,
  `employee_position` varchar(50) DEFAULT NULL,
  `employee_rate` varchar(15) DEFAULT NULL,
  `employee_cost_rate` varchar(15) DEFAULT NULL,
  `employee_id` varchar(100) DEFAULT NULL,
  `employee_whitecard` varchar(100) DEFAULT NULL,
  `employee_address` varchar(500) DEFAULT NULL,
  `employee_address_line1` varchar(200) DEFAULT NULL,
  `employee_address_suburb` varchar(200) DEFAULT NULL,
  `employee_address_state` varchar(5) DEFAULT NULL,
  `employee_address_postcode` varchar(5) DEFAULT NULL,
  `employee_address_country` varchar(200) DEFAULT NULL,
  `employee_birthday` date DEFAULT NULL,
  `employee_startdate` date DEFAULT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `modified_in` varchar(25) DEFAULT NULL,
  `xero_uid` varchar(100) DEFAULT NULL,
  `quickbooks_uid` varchar(100) DEFAULT NULL,
  `myob_uid` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `xero_status` varchar(50) DEFAULT NULL,
  `source` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `_relationship_db_employee`
--

INSERT INTO `_relationship_db_employee` (`id`, `client_id`, `employee_name`, `employee_lastname`, `employee_number`, `employee_email`, `employee_phone`, `employee_fax`, `employee_mobile`, `employee_position`, `employee_rate`, `employee_cost_rate`, `employee_id`, `employee_whitecard`, `employee_address`, `employee_address_line1`, `employee_address_suburb`, `employee_address_state`, `employee_address_postcode`, `employee_address_country`, `employee_birthday`, `employee_startdate`, `date_modified`, `modified_by`, `modified_in`, `xero_uid`, `quickbooks_uid`, `myob_uid`, `status`, `xero_status`, `source`) VALUES
(1, 0, 'Franco', 'Martin', '123-456789', 'asd@gmail.com', '666-66-66', '666-66-66', '09123456789', NULL, NULL, NULL, NULL, NULL, 'Cordillera St., Manila, Philippines ', 'Cordillera St.', 'Manila', NULL, '1016', 'Philippines', '1999-07-04', '2018-12-12', '2018-12-18 04:56:02', 0, NULL, NULL, '19', NULL, NULL, NULL, NULL),
(8, 0, 'Ronaldo', 'Doromal', '123-456789', 'ronaldo@gmail.com', '666-66-66', '666-66-66', '09123456789', NULL, NULL, NULL, NULL, NULL, 'asd street', 'asd street', 'Manila', NULL, '1013', 'Philippines', '1998-07-11', '2018-12-12', '2018-12-20 03:53:09', 0, NULL, NULL, '70', NULL, NULL, NULL, NULL),
(9, 0, 'John', 'Johnson', '', '', '(540) 555-9645', '', '999-99-99', NULL, NULL, NULL, NULL, NULL, 'Elm Street, Town n Country, Canada', 'Elm Street', 'Town n Country', NULL, '1016', 'Canada', '2018-12-15', '2018-10-08', '2018-12-20 03:52:53', 0, NULL, NULL, '54', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `_relationship_db_purchase`
--

CREATE TABLE `_relationship_db_purchase` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `supplier_subcontractor_id` int(11) DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `invoice_date` varchar(100) DEFAULT NULL,
  `due_date` varchar(100) DEFAULT NULL,
  `invoice_attachment` varchar(100) DEFAULT NULL,
  `account_type_id` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `expense_type` int(11) DEFAULT NULL,
  `quickbooks_uid` int(11) DEFAULT NULL,
  `date_moved` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `_relationship_db_purchase`
--

INSERT INTO `_relationship_db_purchase` (`id`, `project_id`, `supplier_subcontractor_id`, `invoice_no`, `invoice_date`, `due_date`, `invoice_attachment`, `account_type_id`, `amount`, `expense_type`, `quickbooks_uid`, `date_moved`) VALUES
(1, 1, 2, 'hjklom', '2018-11-04', '2018-11-04', NULL, 94, 47777, 1, 196, '0000-00-00'),
(2, 1, 1, '', '2018-12-25', '2018-12-25', NULL, 103, 500, 1, 201, '0000-00-00'),
(3, 3, 3, '', '2018-10-30', '2018-10-30', NULL, 17, 20, 1, 200, '0000-00-00'),
(4, 2, 5, '', '2019-01-02', '2019-01-02', NULL, 104, 10, 1, NULL, '0000-00-00'),
(6, 4, 3, 'qwerty2', '2018-01-03', '2018-01-03', NULL, 101, 4590, 1, NULL, '0000-00-00'),
(7, NULL, NULL, '', '2018-10-30', '2018-10-30', NULL, NULL, 1234, NULL, 171, '0000-00-00'),
(8, NULL, NULL, '', '2019-01-02', '2019-01-02', NULL, NULL, 2450, NULL, 176, '0000-00-00'),
(9, NULL, NULL, '', '2019-01-02', '2019-01-02', NULL, NULL, 10, NULL, 198, '0000-00-00'),
(10, NULL, NULL, '', '2018-01-03', '2018-01-03', NULL, NULL, 4590, NULL, 203, '0000-00-00'),
(11, NULL, NULL, '', '2018-01-03', '2018-01-03', NULL, NULL, 4590, NULL, 211, '0000-00-00'),
(12, NULL, NULL, '', '2019-01-05', '2019-01-05', NULL, NULL, 2670, NULL, 178, '0000-00-00'),
(13, NULL, NULL, '', '2019-01-02', '2019-01-02', NULL, NULL, 10, 2, 213, '2019-01-05'),
(14, NULL, NULL, '', '2018-01-03', '2018-01-03', NULL, NULL, 4590, 2, 213, '2019-01-05'),
(15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2019-01-05');

-- --------------------------------------------------------

--
-- Table structure for table `_relationship_db_suppliers`
--

CREATE TABLE `_relationship_db_suppliers` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL DEFAULT '0',
  `supplier_name` varchar(100) DEFAULT NULL,
  `supplier_abn` varchar(50) DEFAULT NULL,
  `supplier_address` varchar(100) DEFAULT NULL,
  `representative_name` varchar(100) DEFAULT NULL,
  `representative_lname` varchar(100) DEFAULT NULL,
  `representative_jobtitle` varchar(50) DEFAULT NULL,
  `representative_phone` varchar(50) DEFAULT NULL,
  `representative_mobile` varchar(50) DEFAULT NULL,
  `representative_fax` varchar(50) DEFAULT NULL,
  `representative_email` varchar(50) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `bank_account_name` varchar(50) DEFAULT NULL,
  `bsb_number` varchar(25) DEFAULT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `modified_in` varchar(25) DEFAULT NULL,
  `xero_uid` varchar(100) DEFAULT NULL,
  `quickbooks_uid` varchar(100) DEFAULT NULL,
  `myob_uid` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `xero_status` varchar(50) DEFAULT NULL,
  `source` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `_relationship_db_suppliers`
--

INSERT INTO `_relationship_db_suppliers` (`id`, `client_id`, `supplier_name`, `supplier_abn`, `supplier_address`, `representative_name`, `representative_lname`, `representative_jobtitle`, `representative_phone`, `representative_mobile`, `representative_fax`, `representative_email`, `bank_account_number`, `bank_account_name`, `bsb_number`, `date_modified`, `modified_by`, `modified_in`, `xero_uid`, `quickbooks_uid`, `myob_uid`, `status`, `xero_status`, `source`) VALUES
(1, 0, 'National Bookstore', NULL, 'Elm Street, Cubao, Quezon City', 'Lhexy', 'Romero', NULL, '999-99-99', '09123456789', '999-99-99', 'leki@gmail.com', '123 XXXX XX X', NULL, NULL, '2018-12-26 04:00:51', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 0, 'Chin', NULL, ', , ', '', '', NULL, '', '', '', '', '', NULL, NULL, '2018-12-26 04:09:03', 0, NULL, NULL, '33', NULL, NULL, NULL, NULL),
(3, 0, 'Brosnahan Insurance Agency', NULL, 'P.O. Box 5, Middlefield, ', 'Nick', 'Brosnahan', NULL, '(650) 555-9912', '(650) 555-9874', '(555) 123-4567', '', '7653412', NULL, NULL, '2018-12-26 04:09:04', 0, NULL, NULL, '31', NULL, NULL, NULL, NULL),
(4, 0, 'Cal Telephone', NULL, '10 Main St., Palo Alto, ', '', '', NULL, '(650) 555-1616', '', '', '', '', NULL, NULL, '2018-12-26 04:09:04', 0, NULL, NULL, '32', NULL, NULL, NULL, NULL),
(5, 0, 'Cigna Health Care', NULL, ', , ', '', '', NULL, '(520) 555-9874', '', '', '', '123456789', NULL, NULL, '2018-12-26 04:09:04', 0, NULL, NULL, '34', NULL, NULL, NULL, NULL),
(6, 0, 'Bob', NULL, ', , ', '', '', NULL, '', '', '', '', '', NULL, NULL, '2018-12-26 04:09:05', 0, NULL, NULL, '56', NULL, NULL, NULL, NULL),
(7, 0, 'Books by Bessie', NULL, '15 Main St., Palo Alto, ', 'Bessie', 'Williams', NULL, '(650) 555-7745', '', '', 'Books@Intuit.com', '1345', NULL, NULL, '2018-12-26 04:09:05', 0, NULL, NULL, '30', NULL, NULL, NULL, NULL),
(8, 0, 'Diego', NULL, ', , ', '', '', NULL, '', '', '', '', '', NULL, NULL, '2018-12-26 04:09:05', 0, NULL, NULL, '36', NULL, NULL, NULL, NULL),
(9, 0, 'Computers by Jenni', NULL, '1515 Main St., Middlefield, ', 'Jenni', 'Winslow', NULL, '(650) 555-8721', '(650) 111-5648', '(650) 999-2663', 'Msfixit@Intuit.com', '', NULL, NULL, '2018-12-26 04:09:06', 0, NULL, NULL, '35', NULL, NULL, NULL, NULL),
(10, 0, 'EDD', NULL, ', , ', '', '', NULL, '', '', '', '', '', NULL, NULL, '2018-12-26 04:09:06', 0, NULL, NULL, '37', NULL, NULL, NULL, NULL),
(11, 0, 'Ellis Equipment Rental', NULL, '45896 Main St., Middlefield, USA', 'Julie', 'Ellis', NULL, '(650) 555-3333', '(650) 445-3333', '', 'Rental@intuit.com', '39765', NULL, NULL, '2018-12-26 04:09:07', 0, NULL, NULL, '38', NULL, NULL, NULL, NULL),
(12, 0, 'Hall Properties', NULL, 'P.O.Box 357, South Orange, ', 'Melanie', 'Hall', NULL, '(973) 555-3827', '(973) 888-6222', '', '', '55642', NULL, NULL, '2018-12-26 04:47:22', 0, NULL, NULL, '40', NULL, NULL, NULL, NULL),
(13, 0, 'Hicks Hardware', NULL, '42 Main St., Middlefield, ', 'Geoff', 'Hicks', NULL, '(650) 554-1973', '(650) 445-6666', '', '', '556223', NULL, NULL, '2018-12-26 04:47:22', 0, NULL, NULL, '41', NULL, NULL, NULL, NULL),
(14, 0, 'Pam Seitz', NULL, 'P.O. Box 15, Bayshore, ', 'Pam', 'Seitz', NULL, '(650) 557-8855', '(650) 888-4446', '(556) 454-5555', 'SeitzCPA@noemail.com', '64132549', NULL, NULL, '2018-12-26 04:47:45', 0, NULL, NULL, '47', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `_supplier_db`
--

CREATE TABLE `_supplier_db` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `_supplier_db`
--

INSERT INTO `_supplier_db` (`supplier_id`, `supplier_name`) VALUES
(1, 'Starbucks'),
(2, 'JCO'),
(3, 'Greenwich'),
(4, 'Perfect White Shirt'),
(5, 'Diagon Alley');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `_account_type_db`
--
ALTER TABLE `_account_type_db`
  ADD PRIMARY KEY (`account_id`);

--
-- Indexes for table `_project_db`
--
ALTER TABLE `_project_db`
  ADD PRIMARY KEY (`project_id`);

--
-- Indexes for table `_relationship_db_customers`
--
ALTER TABLE `_relationship_db_customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `_relationship_db_employee`
--
ALTER TABLE `_relationship_db_employee`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `_relationship_db_purchase`
--
ALTER TABLE `_relationship_db_purchase`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_type_id` (`account_type_id`),
  ADD KEY `_relationship_db_purchase_ibfk_2` (`supplier_subcontractor_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `_relationship_db_suppliers`
--
ALTER TABLE `_relationship_db_suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `_supplier_db`
--
ALTER TABLE `_supplier_db`
  ADD PRIMARY KEY (`supplier_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `_account_type_db`
--
ALTER TABLE `_account_type_db`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `_project_db`
--
ALTER TABLE `_project_db`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `_relationship_db_customers`
--
ALTER TABLE `_relationship_db_customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `_relationship_db_employee`
--
ALTER TABLE `_relationship_db_employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `_relationship_db_purchase`
--
ALTER TABLE `_relationship_db_purchase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `_relationship_db_suppliers`
--
ALTER TABLE `_relationship_db_suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `_supplier_db`
--
ALTER TABLE `_supplier_db`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `_relationship_db_purchase`
--
ALTER TABLE `_relationship_db_purchase`
  ADD CONSTRAINT `_relationship_db_purchase_ibfk_1` FOREIGN KEY (`account_type_id`) REFERENCES `_account_type_db` (`account_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `_relationship_db_purchase_ibfk_2` FOREIGN KEY (`supplier_subcontractor_id`) REFERENCES `_supplier_db` (`supplier_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `_relationship_db_purchase_ibfk_3` FOREIGN KEY (`project_id`) REFERENCES `_project_db` (`project_id`) ON DELETE NO ACTION ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
