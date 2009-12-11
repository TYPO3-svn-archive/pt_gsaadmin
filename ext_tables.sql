#
# Table structure for table 'tx_ptgsaadmin_virtualarticle'
# 
# This is only a dummy table which has to be created to suppress a warning when working with the virtual article
# There is no TCA for this table in ext_tables.php and tca.php, but in tca_virtual_tables.php that is used in the article module to generate form parts
#
CREATE TABLE tx_ptgsaadmin_virtualarticle (
    uid int(11) NOT NULL auto_increment,
    
    PRIMARY KEY (uid),
);