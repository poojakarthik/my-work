	
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
					
					if (element.className == "input-ACN")
					{
						element.validation_ACN = new validation_ACN (element);
					}
				}
			}
		},
		true
	);
	
	function validation_ACN (element)
	{
		this.element = element;
		
		this.init = function ()
		{
			this.element.addEventListener (
				"keyup",
				function (e)
				{
					e.target.validation_ACN.check ();
				},
				true
			);
			
			this.check ();
		}
		
		this.check = function ()
		{
			// 1. If the length is 0, it is valid because we might not have an ACN
			
			if (this.element.value.length == 0)
			{
				this.element.className = "input-ACN";
				return true;
			}
			
			// 2. Check that the item has only Numbers and Spaces
			if (this.element.value.match (/[^\d\s]/g) !== null)
			{
				this.element.className = "input-ACN-invalid";
				return false;
			}
			
			var ACN_NoSpaces = this.element.value.replace (/[^\d]/g, '');
			
			// 3. Check there are 9 integers
			if (ACN_NoSpaces.length > 9)
			{
				this.element.className = "input-ACN-invalid";
				return false;
			}
			
			if (ACN_NoSpaces.length < 9)
			{
				this.element.className = "input-ACN-incomplete";
				return false;
			}
			
			// 1. Apply weighting to digits 1 to 8
			// 2. Sum the products
			// 3. Divide by 10 to obtain remainder 84 / 10 = 8 remainder 4
			// 4. Complement the remainder to 10 10 - 4 = 6 (if complement = 10, set to 0)
			// 5. Check the calculated check digit equals actual check digit
			
			var arrWeights = new Array (8);
			arrWeights [0] = 8;
			arrWeights [1] = 7;
			arrWeights [2] = 6;
			arrWeights [3] = 5;
			arrWeights [4] = 4;
			arrWeights [5] = 3;
			arrWeights [6] = 2;
			arrWeights [7] = 1;
			
			// 1. Apply weighting to digits 1 to 8
			// 2. Sum the products
			var NumberSum = 0;
			
			for (i=0; i < 8; ++i)
			{
				NumberSum += parseInt (ACN_NoSpaces.charAt (i) * arrWeights [i]);
			}
			
			// 3. Divide by 10 to obtain remainder 84 / 10 = 8 remainder 4
			var Remainder = NumberSum % 10;
			
			// 4. Complement the remainder to 10 10 - 4 = 6 (if complement = 10, set to 0)
			var Complement = 10 - Remainder;
			
			if (Complement == 10)
			{
				Complement = 0;
			}
			
			// 5. Check the calculated check digit equals actual check digit
			if (ACN_NoSpaces.charAt (8) != Complement)
			{
				this.element.className = "input-ACN-invalid";
				return false;
			}
			
			this.element.className = "input-ACN-valid";
			return true;
		}
		
		this.init ();
	}
	
