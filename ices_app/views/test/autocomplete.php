<script>
            var countries = {};
                
            var countriesArray = null;
            
            $("#chapter_autocomplete").keypress(function(){
                reset();
            });
            var reset = function(){
                $("#chapter_id").val("");
                countries={"AD": "Andorra",
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
                
                $("#chapter_autocomplete").autocomplete({
                    lookup: countriesArray,
                    minChars: 0,
                    onSelect: function (suggestion) {
                        $("#chapter_id").val(suggestion.data);
                        
                    }                    
                });
            };
</script>            
            
        