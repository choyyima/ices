<script>
            var countries = {};
            var countriesArray = null;
            countries={
                "AD": "Andorra",
                "AE": "United Arab Emirates",
                "AF": "Afghanistan",
                "AG": "Antigua and Barbuda",
                "AI": "Anguilla",
                "AL": "Albania",
                "AM": "Armenia",
                "AN": "Netherlands Antilles",
                "AO": "Angola",
                "AQ": "Antarctica",
                "AR": "Argentina",
                "AS": "American Samoa",
                "AT": "Austria",
                "AU": "Australia",
                "AW": "Aruba",
                "AX": "\u00c5land Islands",
                "AZ": "Azerbaijan",
                "BA": "Bosnia and Herzegovina",
                "BB": "Barbados",
                "BD": "Bangladesh",
                "BE": "Belgium",
                "BF": "Burkina Faso",
                "BG": "Bulgaria",
                "BH": "Bahrain",
                "BI": "Burundi",
                "BJ": "Benin",
                "BL": "Saint Barth\u00e9lemy",
                "BM": "Bermuda",
                "BN": "Brunei",
                "BO": "Bolivia",
                "BQ": "British Antarctic Territory",
                "BR": "Brazil"
            };
            countriesArray = $.map(countries, function (value, key) { return { value: value, data: key }; });
                    
            var substringMatcher = function(strs) {
                return function findMatches(q, cb) {
                  var matches, substringRegex;

                  // an array that will be populated with substring matches
                  matches = [];

                  // regex used to determine if a string contains the substring `q`
                  substrRegex = new RegExp(q, 'i');

                  // iterate through the pool of strings and for any string that
                  // contains the substring `q`, add it to the `matches` array
                  $.each(strs, function(i, str) {
                    if (substrRegex.test(str)) {
                      // the typeahead jQuery plugin expects suggestions to a
                      // JavaScript object, refer to typeahead docs for more info
                      matches.push({ value: str });
                    }
                  });

                  cb(matches);
                };
              };

              var states = ['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California',
                'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii',
                'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana',
                'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota',
                'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire',
                'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota',
                'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island',
                'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont',
                'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'
              ];

              $('#chapter_autocomplete').typeahead({
                hint: true,
                highlight: true,
                minLength: 1
              },
              {
                name: 'states',
                displayKey: 'value',
                source: substringMatcher(states)
              });            
            
</script>            
            
        