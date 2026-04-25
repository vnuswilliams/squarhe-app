<?php

namespace App\Enums;
use App\Concerns\EnumTrait;


enum PermissionEnum: string
{

    use EnumTrait;

    case CRETATE_EMPLOYEE = 'create_employee';
    case UPDATE_EMPLOYEE = 'update_employee';
    case DELETE_EMPLOYEE = 'delete_employee';
    case CREATE_COMPANY = 'create_company';
    case UPDATE_COMPANY = 'update_company';
    case DELETE_COMPANY = 'delete_company';
    case UPDATE_ROLE = 'update_role';
    case UPDATE_COMPANY_SETTING = 'update_company_setting';
    case CREATE_CONTRACT = 'create_contract';
    case UPDATE_CONTRACT = 'update_contract';
    case DELETE_CONTRACT = 'delete_contract';
    case VIEW_DOCUMENT = 'view_document';
    case CREATE_DOCUMENT = 'create_document';
    case UPDATE_DOCUMENT = 'update_document';
    case DELETE_DOCUMENT = 'delete_document';
    case CREATE_LEAVE = 'create_leave';
    case UPDATE_LEAVE = 'update_leave';
    case DELETE_LEAVE = 'delete_leave';
    case CREATE_OVERTIME = 'create_overtime';
    case UPDATE_OVERTIME = 'update_overtime';
    case DELETE_OVERTIME = 'delete_overtime';
    case CREATE_REMUNERATION = 'create_remuneration';
    case UPDATE_REMUNERATION = 'update_remuneration';
    case DELETE_REMUNERATION = 'delete_remuneration';
    case VALIDATED_PAYSLIP ='validated_payslip';
    case DOWNLOAD_PAYSLIP ='download_payslip';
    case VALIDATE_PAYROLL ='validate_payroll';
    case DOWNLOAD_PAYROLL ='download_payroll';

    public function ownerPermission(): string
    {
        return match ($this) {
            self::CRETATE_EMPLOYEE => 'create employee',
            self::UPDATE_EMPLOYEE => 'update employee',
            self::DELETE_EMPLOYEE => 'delete employee',
            self::CREATE_COMPANY => 'create company',
            self::UPDATE_COMPANY => 'update company',
            self::DELETE_COMPANY => 'delete company',
            self::UPDATE_ROLE => 'update role',
            self::UPDATE_COMPANY_SETTING => 'update company setting',
            self::CREATE_CONTRACT => 'create contract',
    self::DELETE_CONTRACT => 'delete contract',
            self::UPDATE_CONTRACT => 'update contract',
            self::VIEW_DOCUMENT => 'view document',
            self::CREATE_DOCUMENT => 'create document',
            self::UPDATE_DOCUMENT => 'update document',
            self::DELETE_DOCUMENT => 'delete document',
            self::CREATE_LEAVE => 'create leave',
            self::UPDATE_LEAVE => 'update leave',
            self::DELETE_LEAVE => 'delete leave',
            self::CREATE_OVERTIME => 'create overtime',
            self::UPDATE_OVERTIME => 'update overtime',
            self::DELETE_OVERTIME => 'delete overtime',
            self::CREATE_REMUNERATION => 'create remuneration',
            self::UPDATE_REMUNERATION => 'update remuneration',
            self::DELETE_REMUNERATION => 'delete remuneration',
            self::VALIDATED_PAYSLIP =>'validated payslip',
            self::DOWNLOAD_PAYSLIP =>'download payslip',
            self::VALIDATE_PAYROLL =>'validate payroll',
            self::DOWNLOAD_PAYROLL =>'download payroll',
        };
    }

    public function adminPermission(): ?string
    {
        return match ($this) {
            self::CRETATE_EMPLOYEE => 'create employee',
            self::UPDATE_EMPLOYEE => 'update employee',
            self::CREATE_CONTRACT => 'create contract',
            self::UPDATE_CONTRACT => 'update contract',
            self::VIEW_DOCUMENT => 'view document',
            self::CREATE_DOCUMENT => 'create document',
            self::UPDATE_DOCUMENT => 'update document',
            self::DELETE_DOCUMENT => 'delete document',
            self::CREATE_LEAVE => 'create leave',
            self::UPDATE_LEAVE => 'update leave',
            self::DELETE_LEAVE => 'delete leave',
            self::CREATE_OVERTIME => 'create overtime',
            self::UPDATE_OVERTIME => 'update overtime',
            self::DELETE_OVERTIME => 'delete overtime',
            self::CREATE_REMUNERATION => 'create remuneration',
            self::UPDATE_REMUNERATION => 'update remuneration',
            self::DELETE_REMUNERATION => 'delete remuneration',
            self::VALIDATED_PAYSLIP =>'validated payslip',
            self::DOWNLOAD_PAYSLIP =>'download payslip',
            self::VALIDATE_PAYROLL =>'validate payroll',
            self::DOWNLOAD_PAYROLL =>'download payroll',
            default => null
        };
    }

    public function managerPermission(): ?string
    {
        return match ($this) {
            self::VIEW_DOCUMENT => 'view document',
            self::CREATE_DOCUMENT => 'create document',
            self::UPDATE_DOCUMENT => 'update document',
            self::CREATE_LEAVE => 'create leave',
            self::UPDATE_LEAVE => 'update leave',
            self::DELETE_LEAVE => 'delete leave',
            self::CREATE_OVERTIME => 'create overtime',
            self::UPDATE_OVERTIME => 'update overtime',
            self::DELETE_OVERTIME => 'delete overtime',
            default => null
        };
    }
}

?>