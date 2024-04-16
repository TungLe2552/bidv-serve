
update ir_model_fields
set name = 'Yêu cầu sửa chữa'
where model_id = (select id  from ir_models where code = 'fleet_vehicle_log_services.create') and code = 'fleet_maintenance_request_id';
update ir_model_fields
set name = 'Yêu cầu sửa chữa'
where model_id = (select id  from ir_models where code = 'fleet_vehicle_log_services.update') and code = 'fleet_maintenance_request_id';
update ir_model_fields
set index = 1
where model_id = (select id  from ir_models where code = 'fleet_vehicles.view') and code = 'type_name';


update ir_model_fields
set index = 2
where model_id = (select id  from ir_models where code = 'fleet_vehicles.view') and code = 'model_name';

update ir_model_fields
set index = 3
where model_id = (select id  from ir_models where code = 'fleet_vehicles.view') and code = 'brand_name';

update ir_model_fields
set index = 4
where model_id = (select id  from ir_models where code = 'fleet_vehicles.view') and code = 'ownership';

update ir_model_fields
set index = 5
where model_id = (select id  from ir_models where code = 'fleet_vehicles.view') and code = 'company_name';
update ir_model_fields
set relation = 'materialized_companies_view'
where model_id = (select id  from ir_models where code = 'fleet_vehicles.view') and code = 'company_name';
update ir_model_fields
set relation_field = 'id:name:company_id'
where model_id = (select id  from ir_models where code = 'fleet_vehicles.view') and code = 'company_name';


update ir_model_fields
set index = 6
where model_id = (select id  from ir_models where code = 'fleet_vehicles.view') and code = 'insurance_term';
update ir_model_fields
set index = 7
where model_id = (select id  from ir_models where code = 'fleet_vehicles.view') and code = 'registration_deadline';


update ir_model_fields
set index = 8
where model_id = (select id  from ir_models where code = 'fleet_vehicles.view') and code = 'tags_name';


update ir_model_fields
set name = 'Thẻ'
where model_id = (select id  from ir_models where code = 'fleet_vehicles.view') and code = 'tags_name';

update ir_model_fields
set index = 9
where model_id = (select id  from ir_models where code = 'fleet_vehicles.view') and code = 'state';

update ir_model_fields
set index = 10
where model_id = (select id  from ir_models where code = 'fleet_vehicles.view') and code = 'employee_name' ;

update ir_model_fields
set relation = 'materialized_driver_employees'
where model_id = (select id  from ir_models where code = 'fleet_vehicles.view') and code = 'employee_name';
update ir_model_fields
set relation_field = 'id:name:driver_id'
where model_id = (select id  from ir_models where code = 'fleet_vehicles.view') and code = 'employee_name';



update ir_mode_filters
set context = '{ "license_plate": [], "state_name": [], "employee_name": [], "company_name": [], "location": [], "weight": [], "max_volume": [], "min_volume": [], "model_name": [], "type_name": [], "brand_name": [], "chassis_number": [], "manufacture_year": [], "color": [], "fuel_type": [], "insurance_term": [], "registration_deadline": [], "acquisition_date": [], "odometer": [], "driver_id": [], "ownership": [], "invoicing_representative_id": [], "vehicle_registration_name": [], "vehicle_owner_id": [], "has_image": [], "image": [], "image_medium": [], "image_small": [], "active": [], "tags_name": []}'
 where model_id = (select id  from ir_models where code = 'fleet_vehicles');

update ir_model_fields
set
  read_only = false
where
  model_id = (
    select
      id
    from
      ir_models
    where
      code = 'fleet_vehicle_log_services.create'
  )
  and code = 'fleet_maintenance_request_id';

  update ir_model_fields
set
  read_only = false
where
  model_id = (
    select
      id
    from
      ir_models
    where
      code = 'fleet_vehicle_log_services.update'
  )
  and code = 'fleet_maintenance_request_id';
DELETE FROM  ir_model_fields WHERE model_id = (select id  from ir_models where code = 'fleet_maintenance_requests.view') and code = 'description';
DELETE FROM  ir_model_fields WHERE model_id = (select id  from ir_models where code = 'fleet_maintenance_requests.update') and code = 'description';
DELETE FROM  ir_model_fields WHERE model_id = (select id  from ir_models where code = 'fleet_maintenance_requests.create') and code = 'description';

