//96-102
FW.Package.create('SP.Sale.SaleType', {}, false);
FW.Package.extend(SP.Sale.SaleType, {

	SALE_TYPE_NEW		:	1,
	SALE_TYPE_EXISTING	:	2,
	SALE_TYPE_WINBACK	:	3

}, true);