Feature: Messaging 

#    Scenario: Prepare 
#         I have test config
#         I stop all mysql services
#         I start mysql service
#         I drop test database
#         I create test database
#         I create table 'messages' in test database
#         I create table 'farms' in test database
#         I create table 'servers' in test database
#         I create table 'server_properties' in test database
#         I create table 'farm_roles' in test database
#         I create table 'farm_settings' in test database
#         I create table 'role_behaviors' in test database
#         I have 10 messages with status 0 and type 'out'
#         I have 10 messages with status 0 and type 'in'
#         I have 10 vpc messages with status 0 and type 'out'
#         I have 10 vpc messages with status 0 and type 'in'

    Scenario: Delivery Ok
        I make prepare
        I have 20 messages with status 0 and type 'out'
        I wait 1 seconds
        I start wsgi server
        I start messaging daemon
        I wait 8 seconds
        I stop messaging daemon
        I stop wsgi server
        I see right messages were delivered

    Scenario: VPC delivery Ok
        I make prepare
        I have 20 vpc messages with status 0 and type 'out'
        I wait 1 seconds
        I start wsgi server
        I start messaging daemon
        I wait 8 seconds
        I stop messaging daemon
        I stop wsgi server
        I see right messages were delivered
 
    Scenario: Delivery Failed
        I make prepare
        I have 5 messages with status 0 and type 'out'
        I have 5 messages with status 0 and type 'in'
        I have 5 vpc messages with status 0 and type 'out'
        I have 5 vpc messages with status 0 and type 'in'
        I wait 1 seconds
        I start messaging daemon
        I wait 8 seconds
        I stop messaging daemon
        I see right messages have 1 handle_attempts

