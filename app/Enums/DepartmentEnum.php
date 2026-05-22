<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum DepartmentEnum: string
{
    use EnumTrait;

    // Direction & Administration
    case EXECUTIVE_MANAGEMENT = 'executive_management';
    case STRATEGIC_PLANNING = 'strategic_planning';
    case CORPORATE_AFFAIRS = 'corporate_affairs';
    case GENERAL_ADMINISTRATION = 'general_administration';
    case BOARD_SECRETARIAT = 'board_secretariat';
    case INTERNAL_COORDINATION = 'internal_coordination';
    case DOCUMENT_CONTROL = 'document_control';
    case ARCHIVES_AND_RECORDS = 'archives_and_records';
    case CORPORATE_GOVERNANCE = 'corporate_governance';
    case BUSINESS_TRANSFORMATION = 'business_transformation';

    // Ressources humaines
    case HUMAN_RESOURCES = 'human_resources';
    case TALENT_ACQUISITION = 'talent_acquisition';
    case RECRUITMENT_OPERATIONS = 'recruitment_operations';
    case PAYROLL_MANAGEMENT = 'payroll_management';
    case EMPLOYEE_RELATIONS = 'employee_relations';
    case LEARNING_AND_DEVELOPMENT = 'learning_and_development';
    case PERFORMANCE_MANAGEMENT = 'performance_management';
    case WORKFORCE_PLANNING = 'workforce_planning';
    case COMPENSATION_AND_BENEFITS = 'compensation_and_benefits';
    case HR_COMPLIANCE = 'hr_compliance';
    case ORGANIZATIONAL_DEVELOPMENT = 'organizational_development';
    case STAFF_WELFARE = 'staff_welfare';
    case OCCUPATIONAL_HEALTH = 'occupational_health';
    case TIME_AND_ATTENDANCE = 'time_and_attendance';
    case HR_ANALYTICS = 'hr_analytics';

    // Finance
    case FINANCIAL_CONTROL = 'financial_control';
    case GENERAL_ACCOUNTING = 'general_accounting';
    case TAX_MANAGEMENT = 'tax_management';
    case TREASURY_OPERATIONS = 'treasury_operations';
    case BUDGET_AND_FORECASTING = 'budget_and_forecasting';
    case AUDIT_AND_ASSURANCE = 'audit_and_assurance';
    case COST_CONTROL = 'cost_control';
    case CREDIT_MANAGEMENT = 'credit_management';
    case BILLING_AND_INVOICING = 'billing_and_invoicing';
    case CASH_FLOW_MANAGEMENT = 'cash_flow_management';
    case INVESTMENT_ANALYSIS = 'investment_analysis';
    case PROCUREMENT_FINANCE = 'procurement_finance';
    case INSURANCE_COORDINATION = 'insurance_coordination';
    case FINANCIAL_REPORTING = 'financial_reporting';
    case EXPENSE_CONTROL = 'expense_control';

    // Juridique
    case LEGAL_ADVISORY = 'legal_advisory';
    case CONTRACT_MANAGEMENT = 'contract_management';
    case REGULATORY_COMPLIANCE = 'regulatory_compliance';
    case LITIGATION_MANAGEMENT = 'litigation_management';
    case CORPORATE_LEGAL_AFFAIRS = 'corporate_legal_affairs';
    case ETHICS_AND_CONDUCT = 'ethics_and_conduct';
    case RISK_AND_COMPLIANCE = 'risk_and_compliance';

    // IT
    case INFORMATION_TECHNOLOGY = 'information_technology';
    case SOFTWARE_ENGINEERING = 'software_engineering';
    case WEB_DEVELOPMENT = 'web_development';
    case MOBILE_DEVELOPMENT = 'mobile_development';
    case INFRASTRUCTURE_AND_NETWORKS = 'infrastructure_and_networks';
    case CLOUD_OPERATIONS = 'cloud_operations';
    case DEVOPS_ENGINEERING = 'devops_engineering';
    case CYBERSECURITY_OPERATIONS = 'cybersecurity_operations';
    case DATABASE_ADMINISTRATION = 'database_administration';
    case TECHNICAL_SUPPORT = 'technical_support';
    case IT_HELPDESK = 'it_helpdesk';
    case SYSTEMS_ADMINISTRATION = 'systems_administration';
    case DATA_ENGINEERING = 'data_engineering';
    case DATA_ANALYTICS = 'data_analytics';
    case BUSINESS_INTELLIGENCE = 'business_intelligence';
    case ARTIFICIAL_INTELLIGENCE = 'artificial_intelligence';
    case MACHINE_LEARNING = 'machine_learning';
    case ERP_MANAGEMENT = 'erp_management';
    case QA_AUTOMATION = 'qa_automation';
    case PRODUCT_ENGINEERING = 'product_engineering';

    // Commercial & Marketing
    case SALES_OPERATIONS = 'sales_operations';
    case KEY_ACCOUNT_MANAGEMENT = 'key_account_management';
    case CUSTOMER_SUCCESS = 'customer_success';
    case BUSINESS_DEVELOPMENT = 'business_development';
    case DIGITAL_MARKETING = 'digital_marketing';
    case BRAND_MANAGEMENT = 'brand_management';
    case MARKET_RESEARCH = 'market_research';
    case PUBLIC_RELATIONS = 'public_relations';
    case CORPORATE_COMMUNICATION = 'corporate_communication';
    case SOCIAL_MEDIA_MANAGEMENT = 'social_media_management';
    case CUSTOMER_SUPPORT_CENTER = 'customer_support_center';
    case TELESALES = 'telesales';
    case EXPORT_SALES = 'export_sales';
    case RETAIL_OPERATIONS = 'retail_operations';
    case ECOMMERCE_OPERATIONS = 'ecommerce_operations';

    // Production & Industrie
    case INDUSTRIAL_PRODUCTION = 'industrial_production';
    case MANUFACTURING_OPERATIONS = 'manufacturing_operations';
    case QUALITY_ASSURANCE = 'quality_assurance';
    case QUALITY_CONTROL = 'quality_control';
    case INDUSTRIAL_MAINTENANCE = 'industrial_maintenance';
    case PROCESS_ENGINEERING = 'process_engineering';
    case FACTORY_SUPERVISION = 'factory_supervision';
    case HEALTH_SAFETY_ENVIRONMENT = 'health_safety_environment';
    case INDUSTRIAL_AUTOMATION = 'industrial_automation';
    case PRODUCTION_PLANNING = 'production_planning';

    // Logistique
    case PROCUREMENT_AND_SUPPLY = 'procurement_and_supply';
    case SUPPLY_CHAIN_MANAGEMENT = 'supply_chain_management';
    case INVENTORY_CONTROL = 'inventory_control';
    case WAREHOUSE_OPERATIONS = 'warehouse_operations';
    case DISTRIBUTION_MANAGEMENT = 'distribution_management';
    case TRANSPORT_COORDINATION = 'transport_coordination';
    case FLEET_MANAGEMENT = 'fleet_management';
    case IMPORT_EXPORT_OPERATIONS = 'import_export_operations';
    case LOGISTICS_PLANNING = 'logistics_planning';
    case CUSTOMS_CLEARANCE = 'customs_clearance';

    // Construction & BTP
    case CIVIL_ENGINEERING = 'civil_engineering';
    case ARCHITECTURAL_DESIGN = 'architectural_design';
    case CONSTRUCTION_SITE_MANAGEMENT = 'construction_site_management';
    case BUILDING_MAINTENANCE = 'building_maintenance';
    case URBAN_PLANNING = 'urban_planning';
    case REAL_ESTATE_OPERATIONS = 'real_estate_operations';
    case PROPERTY_MANAGEMENT = 'property_management';

    // Santé
    case MEDICAL_SERVICES = 'medical_services';
    case NURSING_SERVICES = 'nursing_services';
    case PHARMACY_OPERATIONS = 'pharmacy_operations';
    case LABORATORY_SERVICES = 'laboratory_services';
    case RADIOLOGY_SERVICES = 'radiology_services';
    case PATIENT_CARE = 'patient_care';
    case HOSPITAL_ADMINISTRATION = 'hospital_administration';
    case PUBLIC_HEALTH = 'public_health';

    // Education
    case ACADEMIC_AFFAIRS = 'academic_affairs';
    case STUDENT_SERVICES = 'student_services';
    case ADMISSIONS_AND_REGISTRATION = 'admissions_and_registration';
    case PEDAGOGICAL_COORDINATION = 'pedagogical_coordination';
    case EXAMINATIONS_AND_RECORDS = 'examinations_and_records';
    case RESEARCH_AND_INNOVATION = 'research_and_innovation';
    case LIBRARY_SERVICES = 'library_services';

    // Agriculture
    case AGRICULTURAL_OPERATIONS = 'agricultural_operations';
    case LIVESTOCK_MANAGEMENT = 'livestock_management';
    case IRRIGATION_SERVICES = 'irrigation_services';
    case AGRONOMY_RESEARCH = 'agronomy_research';
    case FISHERIES_MANAGEMENT = 'fisheries_management';
    case FOOD_PROCESSING = 'food_processing';

    // Energie & Mines
    case ENERGY_DISTRIBUTION = 'energy_distribution';
    case POWER_GENERATION = 'power_generation';
    case OIL_AND_GAS_OPERATIONS = 'oil_and_gas_operations';
    case MINING_OPERATIONS = 'mining_operations';
    case RENEWABLE_ENERGY = 'renewable_energy';
    case GEOLOGICAL_SERVICES = 'geological_services';

    // Télécoms
    case NETWORK_OPERATIONS_CENTER = 'network_operations_center';
    case TELECOMMUNICATIONS_ENGINEERING = 'telecommunications_engineering';
    case BROADBAND_SERVICES = 'broadband_services';
    case SATELLITE_COMMUNICATIONS = 'satellite_communications';
    case MOBILE_NETWORK_OPERATIONS = 'mobile_network_operations';

    // Banque & Assurance
    case RETAIL_BANKING = 'retail_banking';
    case CORPORATE_BANKING = 'corporate_banking';
    case MICROFINANCE_OPERATIONS = 'microfinance_operations';
    case LOAN_RECOVERY = 'loan_recovery';
    case UNDERWRITING_SERVICES = 'underwriting_services';
    case CLAIMS_MANAGEMENT = 'claims_management';
    case ACTUARIAL_ANALYSIS = 'actuarial_analysis';

    // Hôtellerie & Tourisme
    case HOTEL_OPERATIONS = 'hotel_operations';
    case FRONT_OFFICE = 'front_office';
    case HOUSEKEEPING_SERVICES = 'housekeeping_services';
    case FOOD_AND_BEVERAGE_SERVICE = 'food_and_beverage_service';
    case EVENT_MANAGEMENT = 'event_management';
    case TOURISM_OPERATIONS = 'tourism_operations';
    case GUEST_RELATIONS = 'guest_relations';

    // Médias & Création
    case GRAPHIC_DESIGN = 'graphic_design';
    case VIDEO_PRODUCTION = 'video_production';
    case AUDIO_PRODUCTION = 'audio_production';
    case CONTENT_CREATION = 'content_creation';
    case EDITORIAL_MANAGEMENT = 'editorial_management';
    case PRINTING_SERVICES = 'printing_services';
    case UI_UX_DESIGN = 'ui_ux_design';
    case ANIMATION_STUDIO = 'animation_studio';

    // ONG & Social
    case COMMUNITY_OUTREACH = 'community_outreach';
    case HUMANITARIAN_PROGRAMS = 'humanitarian_programs';
    case SOCIAL_SERVICES = 'social_services';
    case VOLUNTEER_COORDINATION = 'volunteer_coordination';
    case FUNDRAISING_AND_PARTNERSHIPS = 'fundraising_and_partnerships';

    // Sécurité
    case CORPORATE_SECURITY = 'corporate_security';
    case SURVEILLANCE_OPERATIONS = 'surveillance_operations';
    case FIRE_SAFETY = 'fire_safety';
    case EMERGENCY_RESPONSE = 'emergency_response';

    // Aviation & Transport
    case AIRPORT_OPERATIONS = 'airport_operations';
    case FLIGHT_OPERATIONS = 'flight_operations';
    case AIRCRAFT_MAINTENANCE = 'aircraft_maintenance';
    case MARITIME_OPERATIONS = 'maritime_operations';
    case PORT_MANAGEMENT = 'port_management';
    case RAILWAY_OPERATIONS = 'railway_operations';

    // Luxe & Mode
    case FASHION_DESIGN = 'fashion_design';
    case TEXTILE_PRODUCTION = 'textile_production';
    case BEAUTY_AND_COSMETICS = 'beauty_and_cosmetics';
    case JEWELRY_PRODUCTION = 'jewelry_production';

    // Divers
    case INNOVATION_LAB = 'innovation_lab';
    case KNOWLEDGE_MANAGEMENT = 'knowledge_management';
    case SUSTAINABILITY = 'sustainability';
    case CHANGE_MANAGEMENT = 'change_management';
    case PARTNERSHIP_DEVELOPMENT = 'partnership_development';
    case FRANCHISE_MANAGEMENT = 'franchise_management';
    case CALL_CENTER_OPERATIONS = 'call_center_operations';
    case FIELD_OPERATIONS = 'field_operations';
    case DISPATCH_COORDINATION = 'dispatch_coordination';
    case CUSTOMER_EXPERIENCE = 'customer_experience';
    case AFTER_SALES_SERVICE = 'after_sales_service';
    case INSPECTION_SERVICES = 'inspection_services';
    case CALIBRATION_SERVICES = 'calibration_services';
    case TESTING_AND_CERTIFICATION = 'testing_and_certification';
    case REPAIR_SERVICES = 'repair_services';
    case FACILITY_MANAGEMENT = 'facility_management';
    case JANITORIAL_SERVICES = 'janitorial_services';
    case PROCUREMENT_AUDIT = 'procurement_audit';
    case STRATEGIC_SOURCING = 'strategic_sourcing';
    case VENDOR_MANAGEMENT = 'vendor_management';
    case B2B_RELATIONS = 'b2b_relations';
    case CRM_MANAGEMENT = 'crm_management';
    case FRAUD_PREVENTION = 'fraud_prevention';
    case INVESTIGATION_UNIT = 'investigation_unit';
    case COMPLIANCE_MONITORING = 'compliance_monitoring';
    case DIGITAL_TRANSFORMATION = 'digital_transformation';
    case SMART_OPERATIONS = 'smart_operations';
    case IOT_ENGINEERING = 'iot_engineering';
    case ROBOTICS_ENGINEERING = 'robotics_engineering';
    case DRONE_OPERATIONS = 'drone_operations';
    case SCIENTIFIC_RESEARCH = 'scientific_research';
    case BIOENGINEERING = 'bioengineering';
    case NANOTECHNOLOGY_RESEARCH = 'nanotechnology_research';

    public function label(): string
    {
        return match ($this) {
            self::EXECUTIVE_MANAGEMENT => __('department.executive_management'),
            self::STRATEGIC_PLANNING => __('department.strategic_planning'),
            self::CORPORATE_AFFAIRS => __('department.corporate_affairs'),
            self::GENERAL_ADMINISTRATION => __('department.general_administration'),
            self::BOARD_SECRETARIAT => __('department.board_secretariat'),
            self::INTERNAL_COORDINATION => __('department.internal_coordination'),
            self::DOCUMENT_CONTROL => __('department.document_control'),
            self::ARCHIVES_AND_RECORDS => __('department.archives_and_records'),
            self::CORPORATE_GOVERNANCE => __('department.corporate_governance'),
            self::BUSINESS_TRANSFORMATION => __('department.business_transformation'),
            self::HUMAN_RESOURCES => __('department.human_resources'),
            self::TALENT_ACQUISITION => __('department.talent_acquisition'),
            self::RECRUITMENT_OPERATIONS => __('department.recruitment_operations'),
            self::PAYROLL_MANAGEMENT => __('department.payroll_management'),
            self::EMPLOYEE_RELATIONS => __('department.employee_relations'),
            self::LEARNING_AND_DEVELOPMENT => __('department.learning_and_development'),
            self::PERFORMANCE_MANAGEMENT => __('department.performance_management'),
            self::WORKFORCE_PLANNING => __('department.workforce_planning'),
            self::COMPENSATION_AND_BENEFITS => __('department.compensation_and_benefits'),
            self::HR_COMPLIANCE => __('department.hr_compliance'),
            self::ORGANIZATIONAL_DEVELOPMENT => __('department.organizational_development'),
            self::STAFF_WELFARE => __('department.staff_welfare'),
            self::OCCUPATIONAL_HEALTH => __('department.occupational_health'),
            self::TIME_AND_ATTENDANCE => __('department.time_and_attendance'),
            self::HR_ANALYTICS => __('department.hr_analytics'),
            self::FINANCIAL_CONTROL => __('department.financial_control'),
            self::GENERAL_ACCOUNTING => __('department.general_accounting'),
            self::TAX_MANAGEMENT => __('department.tax_management'),
            self::TREASURY_OPERATIONS => __('department.treasury_operations'),
            self::BUDGET_AND_FORECASTING => __('department.budget_and_forecasting'),
            self::AUDIT_AND_ASSURANCE => __('department.audit_and_assurance'),
            self::COST_CONTROL => __('department.cost_control'),
            self::CREDIT_MANAGEMENT => __('department.credit_management'),
            self::BILLING_AND_INVOICING => __('department.billing_and_invoicing'),
            self::CASH_FLOW_MANAGEMENT => __('department.cash_flow_management'),
            self::INVESTMENT_ANALYSIS => __('department.investment_analysis'),
            self::PROCUREMENT_FINANCE => __('department.procurement_finance'),
            self::INSURANCE_COORDINATION => __('department.insurance_coordination'),
            self::FINANCIAL_REPORTING => __('department.financial_reporting'),
            self::EXPENSE_CONTROL => __('department.expense_control'),
            self::LEGAL_ADVISORY => __('department.legal_advisory'),
            self::CONTRACT_MANAGEMENT => __('department.contract_management'),
            self::REGULATORY_COMPLIANCE => __('department.regulatory_compliance'),
            self::LITIGATION_MANAGEMENT => __('department.litigation_management'),
            self::CORPORATE_LEGAL_AFFAIRS => __('department.corporate_legal_affairs'),
            self::ETHICS_AND_CONDUCT => __('department.ethics_and_conduct'),
            self::RISK_AND_COMPLIANCE => __('department.risk_and_compliance'),
            self::INFORMATION_TECHNOLOGY => __('department.information_technology'),
            self::SOFTWARE_ENGINEERING => __('department.software_engineering'),
            self::WEB_DEVELOPMENT => __('department.web_development'),
            self::MOBILE_DEVELOPMENT => __('department.mobile_development'),
            self::CLOUD_OPERATIONS => __('department.cloud_operations'),
            self::CYBERSECURITY_OPERATIONS => __('department.cybersecurity_operations'),
            self::DATA_ANALYTICS => __('department.data_analytics'),
            self::BUSINESS_INTELLIGENCE => __('department.business_intelligence'),
            self::ARTIFICIAL_INTELLIGENCE => __('department.artificial_intelligence'),
            self::SALES_OPERATIONS => __('department.sales_operations'),
            self::DIGITAL_MARKETING => __('department.digital_marketing'),
            self::BRAND_MANAGEMENT => __('department.brand_management'),
            self::CUSTOMER_SUPPORT_CENTER => __('department.customer_support_center'),
            self::INDUSTRIAL_PRODUCTION => __('department.industrial_production'),
            self::QUALITY_ASSURANCE => __('department.quality_assurance'),
            self::INDUSTRIAL_MAINTENANCE => __('department.industrial_maintenance'),
            self::PROCUREMENT_AND_SUPPLY => __('department.procurement_and_supply'),
            self::SUPPLY_CHAIN_MANAGEMENT => __('department.supply_chain_management'),
            self::WAREHOUSE_OPERATIONS => __('department.warehouse_operations'),
            self::INFRASTRUCTURE_AND_NETWORKS => __('department.infrastructure_and_networks'),
            self::DEVOPS_ENGINEERING => __('department.devops_engineering'),
            self::DATABASE_ADMINISTRATION => __('department.database_administration'),
            self::TECHNICAL_SUPPORT => __('department.technical_support'),
            self::IT_HELPDESK => __('department.it_helpdesk'),
            self::SYSTEMS_ADMINISTRATION => __('department.systems_administration'),
            self::DATA_ENGINEERING => __('department.data_engineering'),
            self::MACHINE_LEARNING => __('department.machine_learning'),
            self::ERP_MANAGEMENT => __('department.erp_management'),
            self::QA_AUTOMATION => __('department.qa_automation'),
            self::PRODUCT_ENGINEERING => __('department.product_engineering'),
            self::KEY_ACCOUNT_MANAGEMENT => __('department.key_account_management'),
            self::CUSTOMER_SUCCESS => __('department.customer_success'),
            self::BUSINESS_DEVELOPMENT => __('department.business_development'),
            self::MARKET_RESEARCH => __('department.market_research'),
            self::PUBLIC_RELATIONS => __('department.public_relations'),
            self::CORPORATE_COMMUNICATION => __('department.corporate_communication'),
            self::SOCIAL_MEDIA_MANAGEMENT => __('department.social_media_management'),
            self::TELESALES => __('department.telesales'),
            self::EXPORT_SALES => __('department.export_sales'),
            self::RETAIL_OPERATIONS => __('department.retail_operations'),
            self::ECOMMERCE_OPERATIONS => __('department.ecommerce_operations'),
            self::MANUFACTURING_OPERATIONS => __('department.manufacturing_operations'),
            self::QUALITY_CONTROL => __('department.quality_control'),
            self::PROCESS_ENGINEERING => __('department.process_engineering'),
            self::FACTORY_SUPERVISION => __('department.factory_supervision'),
            self::HEALTH_SAFETY_ENVIRONMENT => __('department.health_safety_environment'),
            self::INDUSTRIAL_AUTOMATION => __('department.industrial_automation'),
            self::PRODUCTION_PLANNING => __('department.production_planning'),
            self::INVENTORY_CONTROL => __('department.inventory_control'),
            self::DISTRIBUTION_MANAGEMENT => __('department.distribution_management'),
            self::TRANSPORT_COORDINATION => __('department.transport_coordination'),
            self::FLEET_MANAGEMENT => __('department.fleet_management'),
            self::IMPORT_EXPORT_OPERATIONS => __('department.import_export_operations'),
            self::LOGISTICS_PLANNING => __('department.logistics_planning'),
            self::CUSTOMS_CLEARANCE => __('department.customs_clearance'),
            self::CIVIL_ENGINEERING => __('department.civil_engineering'),
            self::ARCHITECTURAL_DESIGN => __('department.architectural_design'),
            self::CONSTRUCTION_SITE_MANAGEMENT => __('department.construction_site_management'),
            self::BUILDING_MAINTENANCE => __('department.building_maintenance'),
            self::URBAN_PLANNING => __('department.urban_planning'),
            self::REAL_ESTATE_OPERATIONS => __('department.real_estate_operations'),
            self::PROPERTY_MANAGEMENT => __('department.property_management'),
            self::NURSING_SERVICES => __('department.nursing_services'),
            self::PHARMACY_OPERATIONS => __('department.pharmacy_operations'),
            self::LABORATORY_SERVICES => __('department.laboratory_services'),
            self::RADIOLOGY_SERVICES => __('department.radiology_services'),
            self::HOSPITAL_ADMINISTRATION => __('department.hospital_administration'),
            self::PUBLIC_HEALTH => __('department.public_health'),
            self::ACADEMIC_AFFAIRS => __('department.academic_affairs'),
            self::STUDENT_SERVICES => __('department.student_services'),
            self::ADMISSIONS_AND_REGISTRATION => __('department.admissions_and_registration'),
            self::PEDAGOGICAL_COORDINATION => __('department.pedagogical_coordination'),
            self::EXAMINATIONS_AND_RECORDS => __('department.examinations_and_records'),
            self::RESEARCH_AND_INNOVATION => __('department.research_and_innovation'),
            self::LIBRARY_SERVICES => __('department.library_services'),
            self::AGRICULTURAL_OPERATIONS => __('department.agricultural_operations'),
            self::LIVESTOCK_MANAGEMENT => __('department.livestock_management'),
            self::IRRIGATION_SERVICES => __('department.irrigation_services'),
            self::AGRONOMY_RESEARCH => __('department.agronomy_research'),
            self::FISHERIES_MANAGEMENT => __('department.fisheries_management'),
            self::FOOD_PROCESSING => __('department.food_processing'),
            self::ENERGY_DISTRIBUTION => __('department.energy_distribution'),
            self::POWER_GENERATION => __('department.power_generation'),
            self::OIL_AND_GAS_OPERATIONS => __('department.oil_and_gas_operations'),
            self::MINING_OPERATIONS => __('department.mining_operations'),
            self::RENEWABLE_ENERGY => __('department.renewable_energy'),
            self::GEOLOGICAL_SERVICES => __('department.geological_services'),
            self::NETWORK_OPERATIONS_CENTER => __('department.network_operations_center'),
            self::TELECOMMUNICATIONS_ENGINEERING => __('department.telecommunications_engineering'),
            self::BROADBAND_SERVICES => __('department.broadband_services'),
            self::SATELLITE_COMMUNICATIONS => __('department.satellite_communications'),
            self::MOBILE_NETWORK_OPERATIONS => __('department.mobile_network_operations'),
            self::RETAIL_BANKING => __('department.retail_banking'),
            self::CORPORATE_BANKING => __('department.corporate_banking'),
            self::MICROFINANCE_OPERATIONS => __('department.microfinance_operations'),
            self::LOAN_RECOVERY => __('department.loan_recovery'),
            self::UNDERWRITING_SERVICES => __('department.underwriting_services'),
            self::CLAIMS_MANAGEMENT => __('department.claims_management'),
            self::ACTUARIAL_ANALYSIS => __('department.actuarial_analysis'),
            self::FRONT_OFFICE => __('department.front_office'),
            self::HOUSEKEEPING_SERVICES => __('department.housekeeping_services'),
            self::EVENT_MANAGEMENT => __('department.event_management'),
            self::TOURISM_OPERATIONS => __('department.tourism_operations'),
            self::GUEST_RELATIONS => __('department.guest_relations'),
            self::GRAPHIC_DESIGN => __('department.graphic_design'),
            self::FOOD_AND_BEVERAGE_SERVICE => __('department.food_and_beverage_service'),
            self::HOTEL_OPERATIONS => __('department.hotel_operations'),
            self::MEDICAL_SERVICES => __('department.medical_services'),
            self::PATIENT_CARE => __('department.patient_care'),
            self::VIDEO_PRODUCTION => __('department.video_production'),
            self::CONTENT_CREATION => __('department.content_creation'),
            self::DIGITAL_TRANSFORMATION => __('department.digital_transformation'),
            self::COMMUNITY_OUTREACH => __('department.community_outreach'),
            self::CORPORATE_SECURITY => __('department.corporate_security'),
            self::FIRE_SAFETY => __('department.fire_safety'),
            self::AIRPORT_OPERATIONS => __('department.airport_operations'),
            self::HUMANITARIAN_PROGRAMS => __('department.humanitarian_programs'),
            self::AUDIO_PRODUCTION => __('department.audio_production'),
            self::EDITORIAL_MANAGEMENT => __('department.editorial_management'),
            self::PRINTING_SERVICES => __('department.printing_services'),
            self::UI_UX_DESIGN => __('department.ui_ux_design'),
            self::ANIMATION_STUDIO => __('department.animation_studio'),
            self::SOCIAL_SERVICES => __('department.social_services'),
            self::VOLUNTEER_COORDINATION => __('department.volunteer_coordination'),
            self::FUNDRAISING_AND_PARTNERSHIPS => __('department.fundraising_and_partnerships'),
            self::SURVEILLANCE_OPERATIONS => __('department.surveillance_operations'),
            self::EMERGENCY_RESPONSE => __('department.emergency_response'),
            self::FLIGHT_OPERATIONS => __('department.flight_operations'),
            self::AIRCRAFT_MAINTENANCE => __('department.aircraft_maintenance'),
            self::PORT_MANAGEMENT => __('department.port_management'),
            self::RAILWAY_OPERATIONS => __('department.railway_operations'),
            self::TEXTILE_PRODUCTION => __('department.textile_production'),
            self::FASHION_DESIGN => __('department.fashion_design'),
            self::MARITIME_OPERATIONS => __('department.maritime_operations'),
            self::BEAUTY_AND_COSMETICS => __('department.beauty_and_cosmetics'),
            self::SUSTAINABILITY => __('department.sustainability'),
            self::INNOVATION_LAB => __('department.innovation_lab'),
            self::JEWELRY_PRODUCTION => __('department.jewelry_production'),
            self::KNOWLEDGE_MANAGEMENT => __('department.knowledge_management'),
            self::CHANGE_MANAGEMENT => __('department.change_management'),
            self::PARTNERSHIP_DEVELOPMENT => __('department.partnership_development'),
            self::FRANCHISE_MANAGEMENT => __('department.franchise_management'),
            self::CALL_CENTER_OPERATIONS => __('department.call_center_operations'),
            self::FIELD_OPERATIONS => __('department.field_operations'),
            self::DISPATCH_COORDINATION => __('department.dispatch_coordination'),
            self::CUSTOMER_EXPERIENCE => __('department.customer_experience'),
            self::AFTER_SALES_SERVICE => __('department.after_sales_service'),
            self::INSPECTION_SERVICES => __('department.inspection_services'),
            self::CALIBRATION_SERVICES => __('department.calibration_services'),
            self::TESTING_AND_CERTIFICATION => __('department.testing_and_certification'),
            self::REPAIR_SERVICES => __('department.repair_services'),
            self::FACILITY_MANAGEMENT => __('department.facility_management'),
            self::JANITORIAL_SERVICES => __('department.janitorial_services'),
            self::PROCUREMENT_AUDIT => __('department.procurement_audit'),
            self::STRATEGIC_SOURCING => __('department.strategic_sourcing'),
            self::VENDOR_MANAGEMENT => __('department.vendor_management'),
            self::B2B_RELATIONS => __('department.b2b_relations'),
            self::CRM_MANAGEMENT => __('department.crm_management'),
            self::FRAUD_PREVENTION => __('department.fraud_prevention'),
            self::INVESTIGATION_UNIT => __('department.investigation_unit'),
            self::COMPLIANCE_MONITORING => __('department.compliance_monitoring'),
            self::SMART_OPERATIONS => __('department.smart_operations'),
            self::IOT_ENGINEERING => __('department.iot_engineering'),
            self::ROBOTICS_ENGINEERING => __('department.robotics_engineering'),
            self::DRONE_OPERATIONS => __('department.drone_operations'),
            self::SCIENTIFIC_RESEARCH => __('department.scientific_research'),
            self::BIOENGINEERING => __('department.bioengineering'),
            self::NANOTECHNOLOGY_RESEARCH => __('department.nanotechnology_research'),
        };
    }
}
