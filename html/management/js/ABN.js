	
	window.addEventListener (
		"load",
		function ()
		{
			for (var i=0; i < document.forms.length; ++i)
			{
				form = document.forms [i];
				
				for (var j=0; j < form.elements.length; ++j)
				{
					element = form.elements [j];
					
					if (element.className == "input-ABN")
					{
						element.validation_ABN = new validation_ABN (element);
					}
				}
			}
		},
		true
	);
	
	function validation_ABN (element)
	{
		this.element = element;
		
		this.init = function ()
		{
			this.element.addEventListener (
				"keyup",
				function (e)
				{
					e.target.validation_ABN.check ();
				},
				true
			);
			
			this.check ();
		}
		
		this.check = function ()
		{
			// 1. If the length is 0, it is valid because we might not have an ABN
			
			if (this.element.value.length == 0)
			{
				this.element.className = "input-ABN";
				return true;
			}
			
			// 2. Check that the item has only Numbers and Spaces
			if (this.element.value.match (/[^\d\s]/g) !== null)
			{
				this.element.className = "input-ABN-invalid";
				return false;
			}
			
			var ABN_NoSpaces = this.element.value.replace (/[^\d]/g, '');
			
			// 3. Check there are 11 integers
			if (ABN_NoSpaces.length > 11)
			{
				this.element.className = "input-ABN-invalid";
				return false;
			}
			
			if (ABN_NoSpaces.length < 11)
			{
				this.element.className = "input-ABN-incomplete";
				return false;
			}
			
			// 4. ABN Calculation
			// http://www.ato.gov.au/businesses/content.asp?doc=/content/13187.htm&pc=001/003/021/002/001&mnu=610&mfp=001/003&st=&cy=1
			
			//   1. Subtract 1 from the first (left) digit to give a new eleven digit number
			//   2. Multiply each of the digits in this new number by its weighting factor
			//   3. Sum the resulting 11 products
			//   4. Divide the total by 89, noting the remainder
			//   5. If the remainder is zero the number is valid
			
			var arrWeights = new Array (11);
			arrWeights [0] = 10;
			arrWeights [1] = 1;
			arrWeights [2] = 3;
			arrWeights [3] = 5;
			arrWeights [4] = 7;
			arrWeights [5] = 9;
			arrWeights [6] = 11;
			arrWeights [7] = 13;
			arrWeights [8] = 15;
			arrWeights [9] = 17;
			arrWeights [10] = 19;
			
			//   1. Subtract 1 from the first (left) digit to give a new eleven digit number
			var NewNumber = "" + (parseInt (ABN_NoSpaces.charAt (0) - 1)) + ABN_NoSpaces.substr (1);
			
			
			//   2. Multiply each of the digits in this new number by its weighting factor
			//   3. Sum the resulting 11 products
			var NumberSum = 0;
			
			for (i=0; i < 11; ++i)
			{
				NumberSum += parseInt (NewNumber.charAt (i) * arrWeights [i]);
			}
			
			//   4. Divide the total by 89, noting the remainder
			//   5. If the remainder is zero the number is valid
			if (NumberSum % 89 != 0)
			{
				this.element.className = "input-ABN-invalid";
				return false;
			}
			
			this.element.className = "input-ABN-valid";
			return true;
		}
		
		this.init ();
	}
	
