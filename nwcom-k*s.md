Kubernetes kicks in as an alternative platform to run an image built with docker build.  So in the case of this write up, the image is jkozik/nwcom.

I am using a kubernetes cluster manager called minikube.  Minikube installs on my host, and when I start it, I create a cluster on a VirtualBox VM.  Minikube is designed to work with VB. 

The VM is running all the building blocks that make up a k8s cluster, but one node on one VM. Once the VM is setup, I control it from my host server using kubectl commands.  

To start, I need to get my image visible to the k8s cluster.  It is already built on my host, but my cluster doesn't see it. I do the following on my host:

```
$ eval $(minikube docker-env)    # set environmnet variable to point to k8s cluster docker repository
$ docker build -t jkozik/nw.com .
$ minikube ssh
  $ docker images                # verify that jkozik/nw.com is found
```
As an alternative, I could build my docker image and put it in the public docker repository. That works, but I don't feel like doing that at this time.

Next, I need to deploy the image.  Here's an excerpt from a yaml file that I will use to deploy this image.
```
apiVersion: apps/v1
kind: Deployment
metadata:
  name: nwcom-depl
  labels:
    app: nwcom
spec:
  replicas: 1
  selector:
    matchLabels:
      app: nwcom
  template:
    metadata:
      labels:
        app: nwcom
    spec:
      containers:
      - name: nwcom
        image: jkozik/nw.com
        imagePullPolicy: Never
 ```         
Here this yaml script will deploy the image.  It will create a deployment called nwcom-depl.  It will create a pod with the same prefix. It will look locally for the image.
```
$ kubectl apply -f nwcom.yaml  
$ kubectl get deployments
$ kubectl get pods
$ kubectl exec -it nwcom-depl-xxxx (pod name) -- /bin/bash  # runs inside the created container/pod
```
The nwcom app needs access to weather data stored on the host at the directory /home/wjr/public_html.  The updated yaml file defines a volume that maps that data to the container running on the k8S cluster.
```
apiVersion: apps/v1
kind: Deployment
metadata:
  name: nwcom-depl
  labels:
    app: nwcom
spec:
  replicas: 1
  selector:
    matchLabels:
      app: nwcom
  template:
    metadata:
      labels:
        app: nwcom
    spec:
      containers:
      - name: nwcom
        image: jkozik/nw.com
        imagePullPolicy: Never
        volumeMounts:
        - mountPath: /var/www/html/mount
          name: mydir
        ports:
        - containerPort: 80
      volumes:
      - name: mydir
        hostPath:
          path: /hosthome/wjr/public_html
          type: Directory
```
The above defines a volume called mydir.  That volume points to storage on the minikube VM /hosthome.  This directory is mapped to the host folder /home.
Reminder:  my nwcom app is running in a container/pod, running in a VM, on my host.  Minikube's /hostname lets me setup a volume that sees the weather directory where my realtime.txt data is stored.
```
$ kubectl apply -f nwcom.yaml
$ kubectl get pods
$ kubectl exec -it nwcom-depl-<pod name> -- /bin/bash
  $ cd /var/ww/html/mount          # Verify that weather data is visible.
```
Ok, so far so good.  For a deployment to become visible to the outside world, it needs to be wrapped in a service.  This is a layer of abstraction that helps manage that a deployment may span multiple pod/containers/nodes.  And that pods can be deployed in replicasets for scaling and reliability.  The service function hides all of that.  In my case, I am using one of everything so it is a little bit of an over kill, but that's the k8s paradigm.  
Here's the updated yaml file to create a service called nmwcom-service.  Note the --- indicates that this is a different function.  In theory, a separate yaml file could have been used.
```
apiVersion: apps/v1
kind: Deployment
metadata:
  name: nwcom-depl
  labels:
    app: nwcom
spec:
  replicas: 1
  selector:
    matchLabels:
      app: nwcom
  template:
    metadata:
      labels:
        app: nwcom
    spec:
      containers:
      - name: nwcom
        image: jkozik/nw.com
        imagePullPolicy: Never
        volumeMounts:
        - mountPath: /var/www/html/mount
          name: mydir
        ports:
        - containerPort: 80
      volumes:
      - name: mydir
        hostPath:
          path: /hosthome/wjr/public_html
          type: Directory
---
apiVersion: v1
kind: Service
metadata:
  name: nwcom-service
spec:
  type: NodePort
  selector:
    app: nwcom
  ports:
  - port: 80
    targetPort: 80
    protocol: TCP
    nodePort: 30080
```
Note: the deployment uses port 80, but the service's nodePort, puts the webpage in the 30000s port range, by design.
To apply the change:
```
$ kubectl apply -f nwcom.yaml
$ kubectl get services
```
From here, we should bring up the service on the minikube desktop.  Switching over to my Remote Desktop connection to my host, I run in a terminal window:
```
$ minikube service list  # verify that nwcom-service exists
$ minikube service nwcom-service
```
This will open up a web browser and display the nwcom app -- my weather website napervilleweather.com.  Note:  I needed to change my default web browser on my host's desktop to firefox for this to work correctly







