# (GCP GKE) HTTP LB + NEGs + INGRESS NGINX 
Step by step guide. All is done in LINUX BASH, but for MAC should be same.
```
gcloud config set project robin-test-project-349009 ; gcloud config get project
```

### define variables
```
CLUSTER_NAME="httplb-negs-nginxingress"
ZONE="europe-west2-b"
echo $CLUSTER_NAME ; echo $ZONE
```
### create the cluster
```
gcloud container clusters create $CLUSTER_NAME --zone $ZONE --machine-type "e2-medium" --enable-ip-alias --num-nodes=2

#check it:

gcloud container clusters list
kubectl config current-context
```

### add the ingress-nginx repo
```
helm repo add ingress-nginx https://kubernetes.github.io/ingress-nginx
helm repo update
```

### create a file values.yaml
```
cat << EOF > values.yaml
controller:
  service:
    type: ClusterIP
    annotations:
      cloud.google.com/neg: '{"exposed_ports": {"80":{"name": "ingress-nginx-80-neg"}}}'
EOF
```
!!! here we probably can add ngx_http_realip_module later, see you at the end of article !!!

and install the ingress-nginx with custom values
```
helm install -f values.yaml ingress-nginx ingress-nginx/ingress-nginx
```

### create the dummy app (also nginx but it not the ingress)
```
cat << EOF > dummy-app-php.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: php
spec:
  selector:
    matchLabels:
      app: dummy
  replicas: 1
  template:
    metadata:
      labels:
        app: dummy
    spec:
      containers:
      - name: php
        image: rahulishu1993/kubephpapp
        ports:
        - name: http
          containerPort: 80
        lifecycle:
          postStart:
            exec:
              command: ["/bin/sh", "-c", 'curl https://raw.githubusercontent.com/robinpecha/hello-world/main/php-header/index.php > /app/index.php']
---
apiVersion: v1
kind: Service
metadata:
  name: dummy-service
spec:
  type: NodePort
  ports:
  - port: 80
    targetPort: 80
  selector:
    app: dummy
EOF
```

### apply the configuration
```
kubectl apply -f dummy-app-php.yaml
```

### Next is to create the ingress object
![](https://via.placeholder.com/15/f03c15/f03c15.png) replace domain in host: "...."  ![](https://via.placeholder.com/15/f03c15/f03c15.png)
(eg. add any domain name to your /etc/hosts/ with current ip of this server to test it localy)
```
cat << EOF > dummy-ingress.yaml
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: dummy-ingress
  annotations:
    kubernetes.io/ingress.class: "nginx"
spec:
  rules:
  - host: "chimtest1.robinpecha.cz"
    http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: dummy-service
            port:
              number: 80
EOF
```


### apply the configuration
```
kubectl apply -f dummy-ingress.yaml
```

### Find the network tags
```
NETWORK_TAGS=$(gcloud compute instances describe $(kubectl get nodes -o jsonpath='{.items[0].metadata.name}') --zone=$ZONE --format="value(tags.items[0])") ; echo $NETWORK_TAGS
```

### Configure the firewall
```
gcloud compute firewall-rules create $CLUSTER_NAME-lb-fw --allow tcp:80 --source-ranges 130.211.0.0/22,35.191.0.0/16 --target-tags $NETWORK_TAGS
```

### add health check configuration
```
gcloud compute health-checks create http app-service-80-health-check --request-path /healthz --port 80 --check-interval 60 --unhealthy-threshold 3 --healthy-threshold 1 --timeout 5
```

### add the backend service
```
gcloud compute backend-services create $CLUSTER_NAME-lb-backend --health-checks app-service-80-health-check --port-name http --global --enable-cdn --connection-draining-timeout 300
```

### add our NEG to the backend service
```
gcloud compute backend-services add-backend $CLUSTER_NAME-lb-backend --network-endpoint-group=ingress-nginx-80-neg --network-endpoint-group-zone=$ZONE --balancing-mode=RATE --capacity-scaler=1.0 --max-rate-per-endpoint=1.0 --global
```

### enable logging
```
gcloud compute backend-services update $CLUSTER_NAME-lb-backend --enable-logging --global
```

### Setup the frontend
```
gcloud compute url-maps create $CLUSTER_NAME-url-map --default-service $CLUSTER_NAME-lb-backend
gcloud compute target-http-proxies create $CLUSTER_NAME-http-proxy --url-map $CLUSTER_NAME-url-map
gcloud compute forwarding-rules create $CLUSTER_NAME-forwarding-rule --global --ports 80 --target-http-proxy $CLUSTER_NAME-http-proxy
```

### Test
But give it some time to deploy ...
```
IP_ADDRESS=$(gcloud compute forwarding-rules describe $CLUSTER_NAME-forwarding-rule --global --format="value(IPAddress)") ; echo $IP_ADDRESS
curl -s -I http://$IP_ADDRESS/
curl -s -I http://35.186.237.180/
```
# To be done
- We still need public ip on nginx, for scraping protection tools etc. We can use something like this:
https://geko.cloud/en/forward-real-ip-to-a-nginx-behind-a-gcp-load-balancer/ .
So we need implement ngx_http_realip_module first. https://nginx.org/en/docs/http/ngx_http_realip_module.html
- try negs in multizonal env
- adapt it to our terraform config
---
# CLEANUP
```
# delete the forwarding-rule aka frontend
gcloud -q compute forwarding-rules delete $CLUSTER_NAME-forwarding-rule --global
# delete the http proxy
gcloud -q compute target-http-proxies delete $CLUSTER_NAME-http-proxy
# delete the url map
gcloud -q compute url-maps delete $CLUSTER_NAME-url-map
# delete the backend
gcloud -q compute backend-services delete $CLUSTER_NAME-lb-backend --global
# delete the health check
gcloud -q compute health-checks delete app-service-80-health-check
# delete the firewall rule
gcloud -q compute firewall-rules delete $CLUSTER_NAME-lb-fw


#havent tried:
helm uninstall ingress-nginx ingress-nginx/ingress-nginx

# probably not needed:
kubectl delete -f dummy-ingress.yaml
kubectl delete -f dummy-app-php.yaml

# delete the cluster
gcloud -q container clusters delete $CLUSTER_NAME --zone=$ZONE
# delete the NEG  
gcloud compute network-endpoint-groups delete ingress-nginx-80-neg --zone=$ZONE
