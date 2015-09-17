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

                
            $("#selector1").select2({

                    minimumInputLength:3
                    ,query:function(query){
                        console.log(query);
                        var data = {results: []}, i, j, s;
                        for (i = 1; i < 5; i++) {
                            s = "";
                            for (j = 0; j < i; j++) {s = s + query.term;}
                            data.results.push({id: query.term + i, text: s});
                        }
                        query.callback(data);
                    }
            }
            );
</script>            
            
        