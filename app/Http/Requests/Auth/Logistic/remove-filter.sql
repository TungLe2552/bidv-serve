update ir_mode_filters
set context = '{ "type_name": [], "model_name": [], "brand_name": [], "ownership": [], "company_name": [], "insurance_term": [], "registration_deadline": [], "tags_name": [], "active": [], "employee_name": []}'
 where model_id = (select id  from ir_models where code = 'im_pickings');


update ir_model_fields
set relation_field = 'id:name:type_name'
where model_id = (select id  from ir_models where code = 'im_pickings.view') and code = 'type_name';

update ir_model_fields
set selection = '[{"text": "1-Mới tạo","value": "1-Mới tạo"},{"text": "2-Đã duyệt","value": "2-Đã duyệt"},{"text": "3-Hủy","value": "3-Hủy"}]'
where model_id = (select id  from ir_models where code = 'im_pickings.view') and code = 'state';

